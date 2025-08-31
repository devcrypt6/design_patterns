<?php
declare(strict_types=1);

final class Song {
    public function __construct(
        public string $title,
        public bool $favorite = false
    ) {}
}

final class Playlist implements IteratorAggregate
{
    private array $songs = [];

    public function add(Song $song): void { $this->songs[] = $song; }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->songs);
    }
}

$pl = new Playlist();
$pl->add(new Song('Blue Moon', favorite: true));
$pl->add(new Song('Autumn Leaves'));
$pl->add(new Song('So What', favorite: true));

foreach ($pl as $song) {
    echo ($song->favorite ? '★ ' : '  ') . $song->title . PHP_EOL;
}
