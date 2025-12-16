<?php

namespace App\Models;

use Eloquent as Model;

/**
 * Class AuctionQuestion
 * @package App\Models
 */
class AuctionQuestion extends Model
{
    public $table = 'auction_questions';

    public $fillable = [
        'auction_id',
        'user_id',
        'question',
        'answer',
        'answered_at',
        'is_public'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'answered_at' => 'datetime'
    ];

    /**
     * Get auction
     */
    public function auction()
    {
        return $this->belongsTo(Auction::class, 'auction_id');
    }

    /**
     * Get questioner
     */
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Check if answered
     */
    public function isAnswered()
    {
        return !is_null($this->answer);
    }

    /**
     * Answer question
     */
    public function answer($answer)
    {
        $this->answer = $answer;
        $this->answered_at = now();
        $this->save();

        // Increment question count
        $this->auction->increment('question_count');
    }
}
