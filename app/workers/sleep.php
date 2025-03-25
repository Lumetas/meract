<?php

use Lum\Core\Worker;

return new class extends Worker {
    public function run(string $message) {
        sleep((int) $message);
        $result = self::sendToServer("Я подождал $message секунд");
        if ($result == "Понял") {
            echo "Меня услышали!\n";
        }
    }
};
