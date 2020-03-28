<?php

declare(strict_types=1);

namespace zonuexe\ZatsuLisp;

use function array_shift;
use Closure;
use function count;
use function is_array;
use function is_callable;
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
            assert(count($sexp) === 2, 'setq accepts only 2 arguments');
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
            }

            return $ret;
        }

        if (isset($sexp[0]) && $sexp[0] === 'let') {
            $new_env = $env;
            array_shift($sexp);
            assert(count($sexp) >= 2, 'let accepts only 2 or more arguments');

            $variables = array_shift($sexp);
            assert(is_array($variables), 'let must receive variable list by 2nd argument');

            foreach ($variables as $v) {
                assert(is_array($v) && count($v) == 2);
                array_unshift($v, 'setq');
                $this->dispatch($v, $new_env);
            }

            $ret = null;
            foreach ($sexp as $s) {
                $ret = $this->dispatch($s, $new_env);
            }

            return $ret;
        }

        if (isset($sexp[0]) && $sexp[0] === 'lambda') {
            array_shift($sexp);
            $variables = array_shift($sexp);
            $args_index = $variables;
            $body = $sexp;

            return function (...$args) use ($args_index, $body, &$env) {
                $new_env = [];
                foreach ($env as $i => &$v) {
                    $new_env[$i] = &$v;
                }
                unset($v);

                foreach ($args as $i => $v) {
                    $name = $args_index[$i];
                    $new_env[$name] = $v;
                }

                return $this->dispatch([
                    'progn',
                    ...$body
                ], $new_env);
            };
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

        if (is_callable($op)) {
            return $op(...$simplefied);
        }

        if ($op === '+') {
            return array_sum($simplefied);
        }

        if ($op === '$') {
            assert(count($simplefied) === 1, '$ operator accepts only 1 argument');
            return $env[$simplefied[0]];
        }

        if ($op === 'array') {
            return $simplefied;
        }

        throw new RuntimeException("'{$op} function is not implemented.");
    }
}
