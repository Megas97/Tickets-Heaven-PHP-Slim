<?php

namespace models;

use models\Event;
use models\Venue;
use models\Ticket;
use models\Currency;
use models\PhoneCode;
use models\UserPermission;
use Illuminate\Database\Eloquent\Model;

class User extends Model {

    protected $table = 'users';

    protected $fillable = [
        'username',
        'email',
        'first_name',
        'last_name',
        'phone_code_id',
        'phone_number',
        'credit_card_number',
        'default_currency_id',
        'address',
        'description',
        'password',
        'profile_picture',
        'settings',
        'active',
        'active_hash',
        'recover_hash',
        'remember_identifier',
        'remember_token',
        'github_id',
        'facebook_id',
    ];

    public function tickets() {

        return $this->hasMany(Ticket::class);
    }

    public function getFullName() {

        return $this->first_name . ' ' . $this->last_name;
    }

    public function setPassword($password) {

        $this->update([
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);
    }

    public function activateAccount() {

        $this->update([
            'active' => true,
            'active_hash' => null,
        ]);
    }

    public function deActivateAccount() {

        $this->update([
            'active' => false,
            'active_hash' => null,
        ]);
    }

    public function updateProfilePicture($profile_picture) {

        $this->update([
            'profile_picture' => $profile_picture,
        ]);
    }

    public function updateRememberCredentials($identifier, $token) {

        $this->update([
            'remember_identifier' => $identifier,
            'remember_token' => $token,
        ]);
    }

    public function removeRememberCredentials() {

        $this->updateRememberCredentials(null, null);
    }

    public function hasPermission($permission) {

        return (bool) $this->permissions->{$permission};
    }

    public function isAdmin() {

        return $this->hasPermission('admin');
    }

    public function isOwner() {

        return $this->hasPermission('owner');
    }

    public function isHost() {

        return $this->hasPermission('host');
    }

    public function isArtist() {

        return $this->hasPermission('artist');
    }

    public function isUser() {

        return $this->hasPermission('user');
    }

    public function permissions() {

        return $this->hasOne(UserPermission::class, 'user_id');
    }

    public function phoneCode() {

        return $this->hasOne(PhoneCode::class, 'id', 'phone_code_id');
    }

    public function defaultCurrency() {

        return $this->hasOne(Currency::class, 'id', 'default_currency_id');
    }

    public function resetPermissions() {

        $permission = UserPermission::where('user_id', $this->id)->first();

        return $permission->update([
            'admin' => 0,
            'owner' => 0,
            'host' => 0,
            'artist' => 0,
            'user' => 0,
        ]);
    }

    public function ownerVenues() {

        return $this->hasMany(Venue::class, 'owner_id', 'id');
    }

    public function hostEvents() {

        return $this->hasMany(Event::class, 'host_id', 'id');
    }

    public function artistEvents() {

        return $this->belongsToMany(Event::class, 'event_participants', 'user_id', 'event_id')->withTimestamps();
    }

    public function boughtTickets() {

        return $this->belongsToMany(Event::class, 'orders', 'user_id', 'event_id')->withPivot('id')->withPivot('ticket_price')->withPivot('currency_id')->withPivot('ticket_quantity')->withPivot('tickets')->withTimestamps()->withTrashed();
    }

    public function setting($setting, $subSetting = null, $subSettingSetting = null) {

        $settings = json_decode($this->settings, true);

        if (!$subSetting && !$subSettingSetting) {
            
            return isset($settings[$setting]) ? $settings[$setting] : null;

        } else if ($subSetting && !$subSettingSetting) {

            return isset($settings[$setting][$subSetting]) ? $settings[$setting][$subSetting] : null;

        } else if ($subSetting && $subSettingSetting) {

            return isset($settings[$setting][$subSetting][$subSettingSetting]) ? $settings[$setting][$subSetting][$subSettingSetting] : null;
        }
    }
}
