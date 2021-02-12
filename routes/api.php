<?php

Route::get('me', 'User\MeController@getMe');

Route::get('designs', 'Designs\DesignController@index');
Route::get('designs/{id}', 'Designs\DesignController@findDesign');
Route::get('designs/{id}/byUser', 'Designs\DesignController@userOwnsDesign');
//users
Route::get('users', 'User\UserController@index');
Route::get('user/{username}', 'User\UserController@findByUsername');
Route::get('users/{id}/designs', 'Designs\DesignController@getForUser');
//team
Route::get('teams/slug/{slug}', 'Teams\TeamsController@findBySlug');
Route::get('teams/{id}/designs', 'Designs\DesignController@getForTeam');
//search designs
Route::get('search/designs', 'Designs\DesignController@search');
Route::get('search/designers', 'User\UserController@search');
Route::get('designs/slug/{slug}', 'Designs\DesignController@findBySlug');

Route::group(['middleware' => ['auth:api']], function(){
      Route::post('logout', 'Auth\LoginController@logout');
      Route::put('settings/profile', 'User\SettingsController@updateProfile');
      Route::put('settings/password', 'User\SettingsController@updatePassword');
      Route::post('designs', 'Designs\UploadController@upload');
      Route::put('designs/{id}', 'Designs\DesignController@update');
      Route::delete('designs/{id}', 'Designs\DesignController@destroy');
      //Teams
      Route::post('teams', 'Teams\TeamsController@store');
      Route::get('teams/{id}', 'Teams\TeamsController@findById');
      Route::get('teams', 'teams\TeamsController@index');
      Route::get('users/teams', 'Teams\TeamsController@fetchUserTeams');
      Route::put('teams/{id}', 'Teams\TeamsController@update');
      Route::delete('teams/{id}', 'Teams\TeamsController@destroy');
      Route::delete('teams/{team_id}/users/{user_id}', 'Teams\TeamsController@removeFromTeam');
      //Invitations
      Route::post('invitations/{teamId}', 'Teams\InvitationsController@invite');
      Route::post('invitations/{id}/resend', 'Teams\InvitationsController@resend');
      Route::post('invitations/{id}/respond', 'Teams\InvitationsController@respond');
      Route::delete('invitations/{id}', 'Teams\InvitationsController@destroy');
      //Likes
      Route::post('designs/{id}/like', 'Designs\DesignController@like');
      Route::get('designs/{id}/liked', 'Designs\DesignController@checkIfUserHasLiked');
      //comments
      Route::post('designs/{id}/comments', 'Designs\CommentController@store');
      Route::put('comments/{id}', 'Designs\CommentController@update');
      Route::delete('comments/{id}', 'Designs\CommentController@destroy');
      //chats
      Route::post('chats', 'Chats\ChatController@sendMessage');
      Route::get('chats', 'Chats\ChatController@getUserChats');
      Route::get('chats/{id}/messages', 'Chats\ChatController@getChatMessages');
      Route::put('chats/{id}/markAsRead', 'Chats\ChatController@markAsRead');
      Route::delete('messages/{id}', 'Chats\ChatController@destroyMessage');
});

Route::group(['middleware' => ['guest:api']], function(){
     Route::post('register', 'Auth\RegisterController@register');
     Route::post('verification/verify/{user}', 'Auth\VerificationController@verify')->name('verification.verify');
     Route::post('verification/resend', 'Auth\VerificationController@resend');
     Route::post('login', 'Auth\LoginController@login');
     Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
     Route::post('password/reset', 'Auth\ResetPasswordController@reset');
});