<?php
class CustomizerForm
{
    static function configToElements($config)
    {
        $elements = [];
        foreach ($config as $field_group) {
            $elements[] = CustomizerForm::buildInputGroup($field_group);
        }
        return $elements;
    }
    
    static function buildInputGroup($group, $args = [], $block_args = [], $field_args = [])
    {
        $key = SiUtils::getRequire($group, SI_KEY);
        $name = SiUtils::getRequire($group, SI_NAME);
        $groups = SiUtils::getRequire($group, SI_CUSTOM_FIELDS);

        $elem = new CustomizerElement($key, $name, $args);
        foreach ($groups as $group) {
            $elem->addChildren(self::buildInputBlock($key, $group, $block_args, $field_args));
        }
        return $elem;
    }
    
    static function buildInputBlock($parent_key, $block, $args = [], $field_args = [])
    {
        $key = self::bond($parent_key, SiUtils::getRequire($block, SI_KEY));
        $name = SiUtils::getRequire($block, SI_NAME);
        $fields = SiUtils::getRequire($block, SI_FIELDS);

        $elem = new CustomizerElement($key, $name, $args);
        foreach ($fields as $field) {
            $field_args[SI_IS_MULTIPLE] = SiUtils::get($block, SI_IS_MULTIPLE, false);
            $elem->addChildren(self::buildInputParts($key, $field, $field_args));
        }

        return $elem;
    }
    
    static function buildInputParts($parent_key, $field, $args = [])
    {
        $input_type = SiUtils::getRequire($field, SI_FIELD_TYPE);
        $choice_values = SiUtils::get($field, SI_FIELD_CHOICE_VALUES, []);
        if ($input_type === SI_FIELD_TYPE_CHECKBOX && count($choice_values) > 1) {
            $multi = '[]';
        } else {
            $multi = SiUtils::get($args, SI_IS_MULTIPLE, false) ? '[]' : '';
        }
        $key = self::bond($parent_key, SiUtils::getRequire($field, SI_KEY));
        $name = SiUtils::getRequire($field, SI_NAME);

        $elem = new CustomizerElement($key, $name, $args);
        $elem->name .= $multi;
        
        // $argsの値を追加
        $elem->addClasses(SiUtils::get($args, SI_ELEM_CLASSES, []));
        $elem->addAttributes(SiUtils::get($args, SI_ELEM_ATTRS, []));
        $elem->addChildren(SiUtils::get($args, SI_ELEM_CHILDREN, []));

        // $fieldの値を追加
        $elem->addClasses(SiUtils::get($field, SI_ELEM_CLASSES, []));
        $elem->addAttributes(SiUtils::get($field, SI_ELEM_ATTRS, []));
        $elem->addChildren(SiUtils::get($field, SI_ELEM_CHILDREN, []));

        // 特殊Attrをセット
        if (SiUtils::get($field, SI_FIELD_IS_REQUIRE, false)) {
            $elem->addAttributes('required');
        }
        $elem->input_type = $input_type;
        $elem->default_value = SiUtils::get($field, SI_DEFAULT, null);
        $elem->choice_values = $choice_values;
        
        return $elem;
    }

    static function bond($key1, $key2)
    {
        return $key1 . SI_BOND . $key2;
    }
}
