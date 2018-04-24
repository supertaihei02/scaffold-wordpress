<?php
class CustomizerElement
{
    public $id = null;
    public $layer_name = null;
    public $name = null;
    public $value = null;
    public $classes = [];
    public $attributes = [];
    public $extras = [];
    public $children = [];
    public $config_path = [];

    // input要素のみ利用
    public $input_type = null;
    public $default_value = null;
    public $choice_values = [];
    public $multiple = false;
    public $multiple_common_id = '';
    public $multiple_last_block = false;
    public $before_block_id = '';
    public $autoload = false;
    public $sequence = 0;
    public $is_data_exist = false;

    function __construct($key, $name = null, $args = [], $config_path = [])
    {
        $this->id = $key;
        $this->name = $key;
        $this->multiple_common_id = $key;
        $this->layer_name = $name;
        $this->value = CustomizerUtils::get($args, SI_ELEM_VALUE, null);
        $this->classes = CustomizerUtils::get($args, SI_ELEM_CLASSES, []);
        $this->attributes = CustomizerUtils::get($args, SI_ELEM_ATTRS, []);
        $this->extras = CustomizerUtils::get($args, SI_EXTRA, []);
        $this->children = CustomizerUtils::get($args, SI_ELEM_CHILDREN, []);
        $this->config_path = $config_path;
    }

    function isInput()
    {
        return !is_null($this->input_type);
    }

    // ------------
    // - Children -
    // ------------
    function hasChild()
    {
        return count($this->children) > 0;
    }

    function addChildren($children)
    {
        foreach (CustomizerUtils::asArray($children) as $child) {
            $this->children[] = $child;
        }
    }

    function removeChildren($child_ids)
    {
        $child_ids = CustomizerUtils::asArray($child_ids);
        $this->children = array_reduce($this->children, function ($reduced, $child) use ($child_ids) {
            /**
             * @var $child CustomizerElement
             */
            if (!in_array($child->id, $child_ids)) {
                $reduced[] = $child;
            }
            return $reduced;
        }, []);
    }

    // --------------
    // - Attributes -
    // --------------
    function getRenderAttributes($attributes)
    {
        $this->addAttributes($attributes);
        return $this->attributes;
    }

    function addAttributes($attributes)
    {
        foreach (CustomizerUtils::asArray($attributes) as $key => $attr) {
            if (is_int($key)) {
                $this->attributes[$attr] = null;
                continue;
            }
            $this->attributes[$key] = empty($attr) ? null :$attr;
        }
    }

    function removeAttributes($attribute_keys)
    {
        foreach (CustomizerUtils::asArray($attribute_keys) as $key) {
            unset($this->attributes[$key]);
        }
    }

    // --------------
    // - Extras -
    // --------------
    function getRenderExtras($extras)
    {
        $this->addExtras($extras);
        return $this->extras;
    }

    function addExtras($extras)
    {
        foreach (CustomizerUtils::asArray($extras) as $key => $attr) {
            if (is_int($key)) {
                $this->extras[$attr] = null;
                continue;
            }
            $this->extras[$key] = empty($attr) ? null :$attr;
        }
    }

    function removeExtras($extra_keys)
    {
        foreach (CustomizerUtils::asArray($extra_keys) as $key) {
            unset($this->extras[$key]);
        }
    }

    // -----------
    // - Classes -
    // -----------
    function getRenderClasses($classes)
    {
        $this->addClasses($classes);
        return $this->classes;
    }

    function addClasses($classes)
    {
        $this->classes = array_reduce(CustomizerUtils::asArray($classes), function ($reduced, $class) {
            if (!in_array($class, $reduced)) {
                $reduced[] = $class;
            }
            return $reduced;
        }, $this->classes);
        return $this->classes;
    }

    function removeClasses($classes)
    {
        $classes = CustomizerUtils::asArray($classes);
        $this->classes = array_reduce($this->classes, function ($reduced, $class) use ($classes){
            if (!in_array($class, $classes)) {
                $reduced[] = $class;
            }
            return $reduced;
        }, []);
        return $this->classes;
    }
}