<?php
declare(strict_types=1);
namespace App\Domain\Model\Common;

// Есть методы и конструкторы, которые должны принимать любое фото, чтобы это было возможно, нужно чтобы все фото реализовали общий интерфейс

interface PhotoInterface extends PhotoVisitorVisitable {
    
    // Здесь будут универсальные для всех фото сигнатуры методов
    function original(): array;
    function extraSmall(): array;
    function small(): array;
    function medium(): array;
    function large(): array;
}
