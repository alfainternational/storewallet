<?php

/*
|--------------------------------------------------------------------------
| Auction Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => ['web']], function () {

    // Public auction routes
    Route::get('/auctions', 'AuctionController@index')->name('auctions.index');
    Route::get('/auctions/{id}', 'AuctionController@show')->name('auctions.show');

    // Authenticated auction routes
    Route::group(['middleware' => ['auth']], function () {

        // Create & manage auctions
        Route::get('/auctions/create', 'AuctionController@create')->name('auctions.create');
        Route::post('/auctions', 'AuctionController@store')->name('auctions.store');
        Route::post('/auctions/{id}/publish', 'AuctionController@publish')->name('auctions.publish');
        Route::post('/auctions/{id}/cancel', 'AuctionController@cancel')->name('auctions.cancel');

        // Bidding
        Route::post('/auctions/{id}/bid', 'AuctionController@placeBid')->name('auctions.bid');
        Route::post('/auctions/{id}/buy-now', 'AuctionController@buyNow')->name('auctions.buy-now');

        // Auto-bidding
        Route::post('/auctions/{id}/auto-bid', 'AuctionController@setupAutoBid')->name('auctions.auto-bid');
        Route::delete('/auctions/{id}/auto-bid', 'AuctionController@removeAutoBid')->name('auctions.remove-auto-bid');

        // Watchlist
        Route::post('/auctions/{id}/watch', 'AuctionController@toggleWatch')->name('auctions.toggle-watch');

        // Questions
        Route::post('/auctions/{id}/questions', 'AuctionQuestionController@store')->name('auctions.questions.store');
        Route::post('/auctions/{auction_id}/questions/{question_id}/answer', 'AuctionQuestionController@answer')->name('auctions.questions.answer');

        // User's auctions
        Route::get('/my/auctions', 'AuctionController@myAuctions')->name('my.auctions');
        Route::get('/my/bids', 'AuctionController@myBids')->name('my.bids');
        Route::get('/my/watchlist', 'AuctionController@myWatchlist')->name('my.watchlist');
        Route::get('/my/won-auctions', 'AuctionController@myWonAuctions')->name('my.won-auctions');
    });

    // API Routes for real-time updates
    Route::group(['prefix' => 'api/auctions', 'middleware' => ['api']], function () {
        Route::get('/{id}/bids', 'API\\AuctionAPIController@getBids');
        Route::get('/{id}/status', 'API\\AuctionAPIController@getStatus');
        Route::get('/ending-soon', 'API\\AuctionAPIController@getEndingSoon');
    });
});
