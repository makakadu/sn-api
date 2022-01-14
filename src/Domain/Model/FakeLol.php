<?php
declare(strict_types=1);
namespace App\Domain\Model;

class FakeLol {
    public string $id;
    public string $name;
    public int $version;
    public FakeKek $kek;
            
    function __construct(FakeKek $kek, string $name) {
        $this->id = (string)\Ulid\Ulid::generate(true);
        $this->name = $name;
        $this->kek = $kek;
    }
}
