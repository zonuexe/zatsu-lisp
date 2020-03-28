<?php

declare(strict_types=1);

namespace zonuexe\ZatsuLisp;

use function array_shift;
use function count;
use function is_array;
use function is_string;
use RuntimeException;

final class Lisp
{
    /**
     * @param array<mixed>
     * @return mixed
     */
    public function eval(array $sexp)
    {
        $env = [];

        return $this->dispatch($sexp, $env);
    }

    /**
     * @return mixed
     */
    public function dispatch(array $sexp, array &$env)
    {
        if (count($sexp) === 0) {
            throw new RuntimeException('Empty sexp not accepted');
        }

        // Special Forms
        if (isset($sexp[0]) && $sexp[0] === 'setq') {
            array_shift($sexp);
            assert(count($sexp) === 2, 'setq accepts only 2 argument');
            $name = array_shift($sexp);
            assert(is_string($name));
            $v = array_shift($sexp);
            $env[$name] = is_array($v) ? $this->dispatch($v, $env) : $v;

            return $env[$name];
        }

        if (isset($sexp[0]) && $sexp[0] === 'progn') {
            array_shift($sexp);
            $ret = null;
            foreach ($sexp as $s) {
                $ret = $this->dispatch($s, $env);
                //var_dump(['s' => $s, 'ret' => $ret]);
            }

            return $ret;
        }

        // Dispatch Functions
        $simplefied = [];
        foreach ($sexp as $a) {
            if (!is_array($a)) {
                $simplefied[] = $a;
                continue;
            }

            $simplefied[] = $this->dispatch($a, $env);
        }

        $op = array_shift($simplefied);

        if ($op === '+') {
            return array_sum($simplefied);
        }

        if ($op === '$') {
            assert(count($simplefied) === 1, '$ operator accepts only 1 argument');
            return $env[$simplefied[0]];
        }

        throw new RuntimeException("'{$op} function is not implemented.");
    }
}
