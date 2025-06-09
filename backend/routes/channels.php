<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Public channel for service request updates
Broadcast::channel('service-requests', function () {
    return true; // Public channel
});

// Public channel for admin notifications (we'll filter on frontend)
Broadcast::channel('admin-notifications', function () {
    return true; // Public channel - filtering will be done on frontend
});

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
