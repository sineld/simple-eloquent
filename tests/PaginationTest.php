<?php

declare(strict_types=1);

class PaginationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        foreach (range(1, 20) as $i) {
            Article::create(['id' => $i, 'title' => 'Title '.$i]);
        }
    }

    public function test_paginate_simple_matches_paginate()
    {
        $eloquent = Article::paginate(5);
        $simple = Article::simple()->paginate(5);

        $this->assertEquals($eloquent->total(), $simple->total());
        $this->assertEquals($eloquent->perPage(), $simple->perPage());
        $this->assertEquals(
            $eloquent->items()[0]->title,
            $simple->items()[0]->title
        );
    }

    public function test_paginate_simple_uses_model_default_per_page_when_omitted()
    {
        $simple = Article::simple()->paginate();

        $this->assertEquals((new Article)->getPerPage(), $simple->perPage());
        $this->assertCount((new Article)->getPerPage(), $simple->items());
    }

    public function test_simple_paginate_simple_matches_simple_paginate()
    {
        $eloquent = Article::simplePaginate(5);
        $simple = Article::simple()->simplePaginate(5);

        $this->assertEquals($eloquent->perPage(), $simple->perPage());
        $this->assertCount(count($eloquent->items()), $simple->items());
        $this->assertEquals($eloquent->hasMorePages(), $simple->hasMorePages());
    }

    public function test_simple_paginate_simple_uses_model_default_per_page_when_omitted()
    {
        $simple = Article::simple()->simplePaginate();

        $this->assertEquals((new Article)->getPerPage(), $simple->perPage());
        $this->assertCount((new Article)->getPerPage(), $simple->items());
    }
}
