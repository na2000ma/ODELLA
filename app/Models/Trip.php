<?php

namespace App\Models;

use App\Enums\GuestStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Date;

/**
 * @property Time time;
 * @property User $supervisor;
 * @property User[] $users;
 * @property TransportationLine[] $lines;
 * @property TransferPosition[] $transferPositions;
 * @property int $id;
 * @property int $status;
 * @property BusDriver $busDriver;
 * @property mixed $evaluations
 * @method isEmpty()
 */
class Trip extends Model
{

    protected $fillable = [
        'supervisor_id',
        'bus_driver_id',
        'time_id',
        'status'
    ];

    protected $appends = [
        'availableSeats'
    ];

    public function availableSeats(): Attribute
    {
        return Attribute::make(
            get: function () {
                $day = Date::now()->dayOfWeek;
                $user_ids = $this->users()->pluck('user_id');
                $busCapacity = $this->busDriver->bus->capacity;
                return
                    $busCapacity -
                    ($this->getManifest($day, $user_ids) + $this->getAcceptedDailyReservations());
            },
        );
    }

    public function getManifest($day, $user_ids): int
    {
        return Program::query()->where('day_id', $day)
            ->where('confirmAttendance1', 1)
            ->whereIn('user_id', $user_ids)
            ->count();
    }

    public function getAcceptedDailyReservations(): int
    {
        return DailyReservation::query()
            ->where('guestRequestStatus', GuestStatus::Approved)
            ->where('trip_id', $this->id)
            ->sum('seatsNumber');
    }

    public function time(): BelongsTo
    {
        return $this->belongsTo(Time::class);
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id', 'id');
    }

    public function busDriver(): BelongsTo
    {
        return $this->belongsTo(BusDriver::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class,
            'trip_users', 'trip_id', 'user_id');
    }

    public function lines(): BelongsToMany
    {
        return $this->belongsToMany(TransportationLine::class,
            'trip_lines', 'trip_id', 'line_id');
    }

    public function transferPositions(): BelongsToMany
    {
        return $this->belongsToMany(TransferPosition::class,
            'trip_positions_times', 'trip_id', 'position_id');

    }

    public function reservations(): HasMany
    {
        return $this->hasMany(DailyReservation::class, "daily_reservation_id");
    }


    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }


    public function lostAndFounds(): HasMany
    {
        return $this->hasMany(Lost_Found::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }
}
