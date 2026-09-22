<?php

namespace App\Services;

use App\Models\Address;
use App\Models\User;

class AddressService
{
    public function create(User $user, array $data): Address
    {
        $isFirstAddress = ! $user->addresses()->exists();

        if ($isFirstAddress || ! empty($data['is_default'])) {
            $this->clearDefault($user);
            $data['is_default'] = true;
        }

        return $user->addresses()->create($data);
    }

    public function update(Address $address, array $data): Address
    {
        $user = $address->user;

        if (! empty($data['is_default'])) {
            $user->addresses()
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        } else {
            $data['is_default'] = false;
        }

        $address->update($data);

        return $address->refresh();
    }

    public function delete(Address $address): void
    {
        $wasDefault = $address->is_default;
        $user = $address->user;

        $address->delete();

        if ($wasDefault) {
            $user->addresses()->first()?->update(['is_default' => true]);
        }
    }

    public function setDefault(Address $address): void
    {
        $this->clearDefault($address->user);
        $address->update(['is_default' => true]);
    }

    private function clearDefault(User $user): void
    {
        $user->addresses()->update(['is_default' => false]);
    }
}
