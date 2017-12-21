<?php

namespace LaxCorp\InnKppBundle\Validator;

/**
 * Class kppValidator
 *
 * @package InnKppBundle\Validator
 */
class kppValidator
{

    const MESSAGE_KPP_EMPTY = 'KPP was empty';
    const MESSAGE_KPP_9_DIGITS = 'KPP may consist of only 9 characters';
    const MESSAGE_KPP_INCORRECT_FORMAT = 'Incorrect format KPP';
    const MESSAGE_KPP_INVALID = 'KPP invalid';

    /**
     * @param      $kpp
     * @param null $message
     *
     * @return bool
     */
    public function validate($kpp, &$message = null)
    {
        $result = false;
        $kpp    = (string)$kpp;
        if (!$kpp) {
            $message = $this::MESSAGE_KPP_EMPTY;
        } elseif (mb_strlen($kpp) !== 9) {
            $message = $this::MESSAGE_KPP_9_DIGITS;
        } elseif (!preg_match('/^[0-9]{4}[0-9A-Z]{2}[0-9]{3}$/', $kpp)) {
            $message = $this::MESSAGE_KPP_INCORRECT_FORMAT;
        } elseif ((int)$kpp === 0) {
            $message = $this::MESSAGE_KPP_INVALID;
        } else {
            $result = true;
        }

        return $result;
    }

}