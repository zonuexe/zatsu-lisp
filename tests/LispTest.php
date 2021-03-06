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
            [
                'sexp' => ['progn', ['+', 1, 1]],
                'expected' => 2,
            ],
            [
                'sexp' => ['progn',
                           ['setq', 'number', ['+', 1, 1]],
                           ['$', 'number']
                ],
                'expected' => 2,
            ],
            [
                'sexp' => ['let',
                           [
                               ['a', 1],
                               ['b', 2],
                           ],
                           ['+', ['$', 'a'], ['$', 'b']],
                ],
                'expected' => 3,
            ],
            [
                'sexp' => ['let',
                           [
                               ['add', ['lambda', ['x', 'y'],
                                        ['+', ['$', 'x'], ['$', 'y']]]],
                               ['a', 1],
                               ['b', 2],
                           ],
                           [['$', 'add'], ['$', 'a'], ['$', 'b']],
                ],
                'expected' => 3,
            ],
            [
                'sexp' => ['array', 1, 2, ['+', 1, 1, 1]],
                'expected' => [1, 2, 3],
            ],
            [
                'sexp' => ['let',
                           [
                               ['constructor', ['lambda', ['v'],
                                                ['lambda', [],
                                                 ['+', 1, ['$', 'v']],
                                                ]]],
                               ['c1', [['$', 'constructor'], 10]],
                               ['c2', [['$', 'constructor'], 20]],
                           ],
                           ['array', [['$', 'c1']], [['$', 'c2']]],
                ],
                'expected' => [11, 21],
            ],
            [
                'sexp' => ['let',
                           [
                               ['constructor', ['lambda', ['v'],
                                                ['lambda', [],
                                                 ['setq', 'v', ['+', 1, ['$', 'v']]],
                                                ]]],
                               ['c1', [['$', 'constructor'], 0]],
                               ['c2', [['$', 'constructor'], 0]],
                           ],
                           [['$', 'c1']],
                           [['$', 'c2']],
                           [['$', 'c1']],
                           [['$', 'c1']],
                           [['$', 'c2']],
                           [['$', 'c1']],
                           ['array', [['$', 'c1']], [['$', 'c2']]],
                ],
                'expected' => [5, 3],
            ],
            [
                'sexp' => ['is_callable', fn() => false],
                'expected' => true,
            ],
        ];
    }
}
