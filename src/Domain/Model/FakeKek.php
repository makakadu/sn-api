<?php
declare(strict_types=1);
namespace App\Domain\Model;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

class FakeKek {
    public string $id;
    public string $name;
    public int $version;
    public Collection $loles;
    
    function __construct(string $name) {
        $this->name = $name;
        $this->id = '01eybshgcqf48w0ndcpx6azj8j';//(string)\Ulid\Ulid::generate(true);
        $this->loles = new ArrayCollection();
  //      $this->loles->add(new FakeLol($this, 'first'));
//        $this->loles->add(new FakeLol($this, 'second'));

    }
}
