<?php
class Bus {
    private $totalPassengers = 0;
    private $occupiedSeats = 0;
    private $totalSeats = 50; // Example total seats in the bus

    public function passengerEnter($number) {
        if ($this->occupiedSeats + $number <= $this->totalSeats) {
            $this->totalPassengers += $number;
            $this->occupiedSeats += $number;
            return "Penumpang masuk: $number. Total penumpang: $this->totalPassengers. Kursi terisi: $this->occupiedSeats.";
        } else {
            return "Tidak cukup kursi untuk $number penumpang.";
        }
    }

    public function passengerExit($number) {
        if ($this->occupiedSeats - $number >= 0) {
            $this->totalPassengers -= $number;
            $this->occupiedSeats -= $number;
            return "Penumpang keluar: $number. Total penumpang: $this->totalPassengers. Kursi terisi: $this->occupiedSeats.";
        } else {
            return "Jumlah penumpang keluar melebihi jumlah penumpang yang ada.";
        }
    }

    public function getTotalPassengers() {
        return $this->totalPassengers;
    }

    public function getOccupiedSeats() {
        return $this->occupiedSeats;
    }
}

// Contoh penggunaan
$bus = new Bus();
echo $bus->passengerEnter(10); // Penumpang masuk: 10
echo $bus->passengerExit(5);    // Penumpang keluar: 5
echo $bus->passengerEnter(50);  // Tidak cukup kursi untuk 50 penumpang.
?>