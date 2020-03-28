<?php

namespace zonuexe\ZatsuLisp;

final class LispTest extends TestCase
{
    private Lisp $lisp;

    public function setUp(): void
    {
        $this->lisp = new Lisp();
    }

    /**
     * @dataProvider lispProvider
     * @param mixed $sexp
     * @param mixed $expected
     */
    public function test($sexp, $expected): void
    {
        $this->assertSame($expected, $this->lisp->eval($sexp));
    }

    public function lispProvider(): array
    {
        return [
            [
                'sexp' => ['+', 1, 1],
                'expected' => 2,
            ],
            [
                'sexp' => ['+', 1, ['+', 2, 3]],
                'expected' => 6,
            ],
        ];
    }
}
