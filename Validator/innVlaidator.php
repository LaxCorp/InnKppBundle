<?php

namespace LaxCorp\InnKppBundle\Validator;

/**
 * Class innVlaidator
 *
 * @package InnKppBundle\Validator
 */
class innVlaidator
{
    const MESSAGE_INN_EMPTY = 'INN was empty';
    const MESSAGE_INN_ONLY_DIGITS = 'INN consists of only digits';
    const MESSAGE_INN_ONLY_10_12_DIGITS = 'INN consists of only 10 or 12 digits';
    const MESSAGE_INN_WRONG_CONTROL_NUMBER = 'Wrong control number';
    const MESSAGE_INN_INVALID = 'INN invalid';

    /**
     * @param      $inn
     * @param null $message
     *
     * @return bool
     */
    public function validate($inn, &$message = null)
    {
        $result = false;
        $inn    = (string)$inn;
        if (!$inn) {
            $message = $this::MESSAGE_INN_EMPTY;
        } elseif (preg_match('/[^0-9]/', $inn)) {
            $message = $this::MESSAGE_INN_ONLY_DIGITS;
        } elseif (!in_array($inn_length = strlen($inn), [10, 12])) {
            $message = $this::MESSAGE_INN_ONLY_10_12_DIGITS;
        } elseif ((int)$inn === 0) {
            $message = $this::MESSAGE_INN_INVALID;
        } else {
            $check_digit = function ($inn, $coefficients) {
                $n = 0;
                foreach ($coefficients as $i => $k) {
                    $n += $k * (int)$inn{$i};
                }

                return $n % 11 % 10;
            };
            switch ($inn_length) {
                case 10:
                    $n10 = $check_digit($inn, [2, 4, 10, 3, 5, 9, 4, 6, 8]);
                    if ($n10 === (int)$inn{9}) {
                        $result = true;
                    }
                    break;
                case 12:
                    $n11 = $check_digit($inn, [7, 2, 4, 10, 3, 5, 9, 4, 6, 8]);
                    $n12 = $check_digit($inn, [3, 7, 2, 4, 10, 3, 5, 9, 4, 6, 8]);
                    if (($n11 === (int)$inn{10}) && ($n12 === (int)$inn{11})) {
                        $result = true;
                    }
                    break;
            }
            if (!$result) {
                $message = $this::MESSAGE_INN_WRONG_CONTROL_NUMBER;
            }
        }

        return $result;
    }

}