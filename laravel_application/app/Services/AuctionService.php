<?php

namespace App\Services;

use App\Models\Auction;
use App\Models\AuctionCategory;
use App\Models\AuctionImage;
use App\Models\AuctionActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class AuctionService
{
    protected $escrowService;
    protected $walletService;

    public function __construct(EscrowService $escrowService, WalletService $walletService)
    {
        $this->escrowService = $escrowService;
        $this->walletService = $walletService;
    }

    /**
     * Create new auction
     *
     * @param array $data
     * @param array|null $images
     * @return Auction
     */
    public function createAuction(array $data, $images = null)
    {
        DB::beginTransaction();

        try {
            // Generate auction number
            $data['auction_number'] = Auction::generateAuctionNumber();
            $data['user_id'] = auth()->id();
            $data['current_price'] = $data['starting_price'];
            $data['status'] = 'draft';

            // Calculate end time if not provided
            if (!isset($data['end_time'])) {
                $duration = $data['duration_minutes'] ?? 4320; // 3 days default
                $data['end_time'] = Carbon::now()->addMinutes($duration);
            }

            // Set start time
            if (!isset($data['start_time'])) {
                $data['start_time'] = Carbon::now();
            }

            // Create auction
            $auction = Auction::create($data);

            // Handle images
            if ($images && is_array($images)) {
                $this->addAuctionImages($auction, $images);
            }

            // Log activity
            AuctionActivityLog::logActivity(
                $auction->id,
                'auction_created',
                'Auction created: ' . $auction->title
            );

            DB::commit();

            return $auction;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create auction: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update auction
     */
    public function updateAuction(Auction $auction, array $data, $images = null)
    {
        DB::beginTransaction();

        try {
            // Only allow updates if auction is draft or scheduled
            if (!in_array($auction->status, ['draft', 'scheduled'])) {
                throw new Exception('Cannot update active or ended auction');
            }

            $auction->update($data);

            // Handle images if provided
            if ($images && is_array($images)) {
                $this->addAuctionImages($auction, $images);
            }

            // Log activity
            AuctionActivityLog::logActivity(
                $auction->id,
                'auction_updated',
                'Auction updated'
            );

            DB::commit();

            return $auction;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update auction: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Publish auction (make it active)
     */
    public function publishAuction(Auction $auction)
    {
        if ($auction->status !== 'draft') {
            throw new Exception('Only draft auctions can be published');
        }

        $auction->status = 'active';
        $auction->start_time = Carbon::now();
        $auction->save();

        // Log activity
        AuctionActivityLog::logActivity(
            $auction->id,
            'auction_published',
            'Auction published and now active'
        );

        return $auction;
    }

    /**
     * Cancel auction
     */
    public function cancelAuction(Auction $auction, $reason = null)
    {
        DB::beginTransaction();

        try {
            // Check if can be cancelled
            if (!in_array($auction->status, ['draft', 'scheduled', 'active'])) {
                throw new Exception('Cannot cancel this auction');
            }

            // If has bids, need to refund
            if ($auction->bid_count > 0 && $auction->escrow_trx) {
                // Refund escrow if payment was made
                // This would be handled by escrow service
            }

            $auction->status = 'cancelled';
            $auction->save();

            // Log activity
            AuctionActivityLog::logActivity(
                $auction->id,
                'auction_cancelled',
                'Auction cancelled' . ($reason ? ': ' . $reason : '')
            );

            DB::commit();

            return $auction;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel auction: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Complete auction and process winner payment
     */
    public function completeAuction(Auction $auction)
    {
        DB::beginTransaction();

        try {
            // Determine winner
            $auction->complete();

            if ($auction->winner_user_id) {
                // Process payment through escrow
                // The winner should have already deposited to escrow when bidding

                // Log activity
                AuctionActivityLog::logActivity(
                    $auction->id,
                    'auction_completed',
                    'Auction completed. Winner: User #' . $auction->winner_user_id,
                    ['winner_id' => $auction->winner_user_id, 'winning_price' => $auction->winning_price]
                );

                // TODO: Send notifications to winner and seller
            } else {
                AuctionActivityLog::logActivity(
                    $auction->id,
                    'auction_expired',
                    'Auction expired with no bids'
                );
            }

            DB::commit();

            return $auction;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to complete auction: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Add images to auction
     */
    protected function addAuctionImages(Auction $auction, array $images)
    {
        foreach ($images as $index => $image) {
            if ($image instanceof \Illuminate\Http\UploadedFile) {
                $path = $image->store('auctions/' . $auction->id, 'public');

                // Create thumbnail (simplified - would use image processing library)
                $thumbnailPath = $path; // TODO: Generate actual thumbnail

                AuctionImage::create([
                    'auction_id' => $auction->id,
                    'image_path' => $path,
                    'thumbnail_path' => $thumbnailPath,
                    'is_primary' => $index === 0,
                    'order' => $index
                ]);
            }
        }
    }

    /**
     * Delete auction image
     */
    public function deleteImage(AuctionImage $image)
    {
        // Delete from storage
        if (Storage::disk('public')->exists($image->image_path)) {
            Storage::disk('public')->delete($image->image_path);
        }

        if ($image->thumbnail_path && Storage::disk('public')->exists($image->thumbnail_path)) {
            Storage::disk('public')->delete($image->thumbnail_path);
        }

        $image->delete();
    }

    /**
     * Watch auction
     */
    public function watchAuction(Auction $auction, $userId = null)
    {
        $userId = $userId ?? auth()->id();

        $watcher = $auction->watchers()->firstOrCreate([
            'user_id' => $userId
        ]);

        if ($watcher->wasRecentlyCreated) {
            $auction->increment('watcher_count');
        }

        return $watcher;
    }

    /**
     * Unwatch auction
     */
    public function unwatchAuction(Auction $auction, $userId = null)
    {
        $userId = $userId ?? auth()->id();

        $deleted = $auction->watchers()->where('user_id', $userId)->delete();

        if ($deleted) {
            $auction->decrement('watcher_count');
        }

        return $deleted;
    }

    /**
     * Get active auctions
     */
    public function getActiveAuctions($filters = [])
    {
        $query = Auction::where('status', 'active')
            ->where('start_time', '<=', now())
            ->where('end_time', '>', now());

        // Apply filters
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['city'])) {
            $query->where(function($q) use ($filters) {
                $q->where('pickup_city', $filters['city'])
                  ->orWhere('delivery_city', $filters['city']);
            });
        }

        if (isset($filters['min_price'])) {
            $query->where('current_price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('current_price', '<=', $filters['max_price']);
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';

        if ($sortBy === 'ending_soon') {
            $query->orderBy('end_time', 'asc');
        } elseif ($sortBy === 'popular') {
            $query->orderBy('bid_count', 'desc');
        } else {
            $query->orderBy($sortBy, $sortOrder);
        }

        return $query->with(['user', 'category', 'images', 'winningBid'])
            ->paginate($filters['per_page'] ?? 20);
    }

    /**
     * Get ending soon auctions
     */
    public function getEndingSoonAuctions($hours = 24)
    {
        return Auction::where('status', 'active')
            ->where('end_time', '>', now())
            ->where('end_time', '<=', now()->addHours($hours))
            ->orderBy('end_time', 'asc')
            ->with(['user', 'category', 'images', 'winningBid'])
            ->get();
    }

    /**
     * Process expired auctions (should be run by scheduler)
     */
    public function processExpiredAuctions()
    {
        $expiredAuctions = Auction::where('status', 'active')
            ->where('end_time', '<=', now())
            ->get();

        foreach ($expiredAuctions as $auction) {
            try {
                $this->completeAuction($auction);
            } catch (Exception $e) {
                Log::error('Failed to process expired auction #' . $auction->id . ': ' . $e->getMessage());
            }
        }

        return $expiredAuctions->count();
    }
}
