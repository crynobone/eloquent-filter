<?php

namespace eloquentFilter\QueryFilter\Detection;

use eloquentFilter\QueryFilter\Detection\ConditionsDetect\WhereCustomCondition;

/**
 * Class DetectorConditions.
 */
class DetectorConditions
{
    /**
     * @var
     */
    private $detector;

    /**
     * DetectorConditions constructor.
     *
     * @param array $detector
     *
     * @throws \ReflectionException
     */
    public function __construct(array $detector)
    {
        foreach ($detector as $detector_obj) {
            $reflect = new \ReflectionClass($detector_obj);
            if ($reflect->implementsInterface(DetectorContract::class)) {
                $this->detector[] = $detector_obj;
            }
        }
    }

    /**
     * @param string $field
     * @param $params
     * @param null $model
     *
     * @throws \Exception
     *
     * @return string|null
     */
    public function detect(string $field, $params, $model = null): ?string
    {
        foreach ($this->detector as $detector_obj) {
            if ($this->handelListFields($field, $model->getWhiteListFilter(), $model->checkModelHasOverrideMethod($field), $model)) {
                $out = $detector_obj::detect($field, $params, $model->checkModelHasOverrideMethod($field));
                if (!empty($out)) {
                    return $out;
                }
            }
        }

        return null;
    }


    /**
     * @param string $field
     * @param array|null $list_white_filter_model
     * @param bool $has_method
     * @param $model_class
     * @return bool
     * @throws \Exception
     */
    private function handelListFields(string $field, ?array $list_white_filter_model, bool $has_method, $model_class)
    {
        if ($output = $this->checkSetWhiteListFields($field, $list_white_filter_model)) {
            return $output;
        } elseif (($field == 'f_params' || $field == 'or') || $has_method) {
            return true;
        }

        $class_name = class_basename($model_class); // todo make test for this

        throw new \Exception("You must set $field in whiteListFilter in $class_name.php
         or create a override method with name $field or call ignoreRequest function for ignore $field.");
    }

    /**
     * @param string     $field
     * @param array|null $query
     *
     * @return bool
     */
    private function checkSetWhiteListFields(string $field, ?array $query): bool
    {
        if (in_array($field, $query) ||
            $query[0] == '*') {
            return true;
        }

        return false;
    }
}
