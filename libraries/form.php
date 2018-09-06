<?php

// input handler functions

function post($variable = null, $validate = false)
{
        if (!$variable)
        {
                $values = [];
                foreach ($_POST as $variable => $value)
                {
                        $values[$variable] = post($variable);
                }
                foreach ($_GET as $variable => $value)
                {
                        $values[$variable] = post($variable);
                }
                foreach ($_FILES as $variable => $value)
                {
                        $values[$variable] = post($variable);
                }

                return $values;
        }

        $value = false;
        if (isset($_POST[$variable]))
        {
                $value = $_POST[$variable];
        }
        elseif (isset($_FILES[$variable]))
        {
                $names = $_FILES[$variable]['name'];
                $paths = $_FILES[$variable]['tmp_name'];
                $sizes = $_FILES[$variable]['size'];
                $types = $_FILES[$variable]['type'];

                foreach ($names as $i => $name)
                {
                        $data = [];
                        $data['name'] = $name;
                        $data['path'] = $paths[$i];
                        $data['type'] = $types[$i];
                        $data['size'] = $sizes[$i];

                        $value[$name] = $data;
                }
        }
        elseif (isset($_GET[$variable]))
        {
                $value = $_GET[$variable];
        }
        elseif (isset($_SESSION[$variable]))
        {
                $value = $_SESSION[$variable];
        }

        if ($validate)
        {
                $options = ['~','|','/','(',')','-','_','+','='];
                foreach ($options as $option)
                {
                        if (!stristr($validate, $option))
                        {
                                return preg_replace($option.'[^'.$validate.']'.$option, '', $value);
                        }
                }
        }

        return $value;
}

function hidden($name, $value, $data = [])
{
        $data['type'] = 'hidden';
        $data['value'] = $value;
        $data = normalize_for_input($name, $data);

        return "<input {$data['vars']} />";
}

function textbox($name, $data = [])
{
        if (stristr($name, 'password') && empty($data['type']))
        {
                $data['type'] = 'password';
                $data['value'] = '';
                $_POST[$name] = '';
        }
        elseif (stristr($name, 'email') && empty($data['type']))
        {
                $data['type'] = 'email';
        }
        $data['type'] = (!empty($data['type']) ? $data['type'] : 'text');
        $data['autocomplete'] = 'off';
        $data = normalize_for_input($name, $data);

        $html = '<div class="textbox">';
        if (empty($data['nolabel']))
        {
                $html .= '<label for="'.$data['id'].'" class="textbox-label">'.$data['label'].':</label>';
        }
        $html .= '<input '.$data['vars'].' />';
        $html .= '</div>';

        return $html;
}

function textarea($name, $data = [])
{
        $value = (post($name) ?: (!empty($data['value']) ? $data['value'] : ''));
        unset($data['value']);
        $data = normalize_for_input($name, $data);

        $html = '<div class="textarea">';
        if (empty($data['nolabel']))
        {
                $html .= '<label for="'.$data['id'].'" class="textbox-label">'.$data['label'].':</label>';
        }
        $html .= '<textarea '.$data['vars'].'>'.$value.'</textarea>';
        $html .= '</div>';

        return $html;
}

function dropdown($name, $data = [])
{
        $value = (!empty($data['value']) ? $data['value'] : '');
        $data = normalize_for_input($name, $data);
        $selected_value = (post($name) ?: $value);
        $options = '';
        $show_value = '';
        foreach ($data['options'] as $value => $label)
        {
                #$options .= '<div class="dropdown-option" data-value="'.$value.'">'.$label.'</div>';
                if ($value == $selected_value)
                {
                        #$show_value = $label;
                        $options .= '<option value="'.$value.'" selected="true">'.$label.'</option>';
                }
                else
                {
                        $options .= '<option value="'.$value.'">'.$label.'</option>';
                }
        }
        #$html = '<div class="dropdown-wrapper">';
        #$html .= '<div class="form-group"><div class="input-group">';
        #if ($icon)
        #{
        #        $html .= '<span class="input-group-addon"><i class="'.$icon.'"></i></span>';
        #}
        #$html .= '<input type="text" class="input form-control pointer dropdown" value="'.$show_value.'" />';
        #$html .= '<span class="input-group-addon dropdown"><i class="glyphicons glyphicons-chevron-down"></i></span>';
        #$html .= '</div></div>';
        #$html .= '<input type="hidden" '.$data['vars'].' />';
        #$html .= '<div class="dropdown-options input">';
        #$html .= $options;
        #$html .= '</div>';
        #$html .= '</div>';
        $html = '';
        if (empty($data['nolabel']))
        {
                $html .= '<label for="'.$data['id'].'" class="textbox-label">'.$data['label'].':</label>';
        }
        $html .= '<select '.$data['vars'].'>'.$options.'</select>';

        return $html;
}

function button($name, $data = [], $url = null)
{
        $data['value'] = (!empty($data['value']) ? $data['value'] : 1);
        $data['class'] = (!empty($data['class']) ? $data['class'].' button' : 'button');
        $data = normalize_for_input($name, $data);

        $html = (!empty($data['indent']) ? "<label></label>" : "");
        $html .= '<button '.$data['vars'].'>'.$data['label'].'</button>';
        if ($url)
        {
                $html = '<a href="'.$url.'">'.$html.'</a>';
        }

        return $html;
}

function checkbox($name, $data = [])
{
        $data['type'] = 'checkbox';
        $data['value'] = (!empty($data['value']) ? $data['value'] : 1);
        if (!empty($data['checked']) || post($name) == $data['value'])
        {
                $data['checked'] = "checked='true'";
        }
        else
        {
                unset($data['checked']);
        }
        $data = normalize_for_input($name, $data);

        $html = '<div class="checkbox">';
        $html .= "<input {$data['vars']} />";
        if (empty($data['nolabel']))
        {
                $html .= '<label for="'.$data['id'].'" class="checkbox-label">'.$data['label'].'</label>';
        }
        $html .= '</div>';

        return $html;
}

function checkboxes($name, $data = [])
{
        $data['type'] = 'checkbox';

        $data = normalize_for_input($name, $data);

        $html = '<div class="checkboxes">';
        if (empty($data['nolabel']))
        {
                $html .= '<label for="'.$data['id'].'" class="textbox-label">'.$data['label'].'</label>';
        }

        $html .= '<table class="no-break">';
        foreach ($data['options'] as $value => $label)
        {
                $id = $data['id'].'-'.$value;

                $checkbox_data = $data;
                $checkbox_data['id'] = $id;
                $checkbox_data['value'] = $value;
                if (!empty($checkbox_data['checked']) || post($name) === $value)
                {
                        $checkbox_data['checked'] = 'true';
                }
                else
                {
                        unset($checkbox_data['checked']);
                }

                $checkbox_data = normalize_for_input($name, $checkbox_data);
                $html .= '<tr><td class="middle"><input '.$checkbox_data['vars'].' /></td><td class="middle"><label for="'.$id.'" class="checkbox-label">'.$label.'</label></td></tr>';
        }
        $html .= '</table>';
        $html .= '</div>';

        return $html;
}

function radios($name, $data = [])
{
        $data['type'] = 'radio';

        $data = normalize_for_input($name, $data);

        $html = '<div class="radios">';
        if (empty($data['nolabel']))
        {
                $html .= '<label for="'.$data['id'].'" class="textbox-label">'.$data['label'].'</label>';
        }

        $html .= '<table class="no-break">';
        foreach ($data['options'] as $value => $label)
        {
                $id = $data['id'].'-'.$value;

                $radio_data = $data;
                $radio_data['id'] = $id;
                $radio_data['value'] = $value;
                if (!empty($radio_data['checked']) || post($name) === $value || (!post() && $value == $data['value']))
                {
                        $radio_data['checked'] = 'true';
                }
                else
                {
                        unset($radio_data['checked']);
                }
                $radio_data = normalize_for_input($name, $radio_data);
                $html .= '<tr><td class="middle"><input '.$radio_data['vars'].' /></td><td class="middle"><label for="'.$id.'" class="checkbox-label">'.$label.'</label></td></tr>';
        }
        $html .= '</table>';
        $html .= '</div>';

        return $html;
}

function upload($name, $data = [], $multiple = false)
{
        $data['type'] = 'file';
        $multiple = ($multiple ? ' multiple' : '');
        $data = normalize_for_input($name, $data);
        $html = '<input '.$data['vars'].$multiple.' />';
        return $html;
}

function normalize_for_input($name, $data = [])
{
        // assign a label if not set already
        if (empty($data['label']))
        {
                $data['label'] = ucwords(deslugify(slugify($name)));
        }

        // give it a good name
        $data['name'] = (isset($data['name']) ? $data['name'] : preg_replace('/[^-[]_a-zA-Z0-9]/', '', $name));

        // every input needs an id
        if (empty($data['id']))
        {
                $data['id'] = str_replace('_', '-', $data['name']);
        }

        // populate with pre-submitted form values
        // if not already submitted, use default value if specified
        // do not give all radios this value since they have the same name
        if (post($name) && (empty($data['type']) || $data['type'] != 'radio'))
        {
                $data['value'] = post($name);
        }
        elseif (!isset($data['value']))
        {
                $data['value'] = '';
        }

        // give all inputs the input css class
        if (!isset($data['class']))
        {
                $data['class'] = 'input';
        }
        else
        {
                $data['class'] = 'input '.$data['class'];
        }

        // append the name to the class
        $data['class'] .= ' '.slugify($data['name']).'';

        // clear up classes
        $unique_classes = [];
        $classes = explode(' ', $data['class']);
        foreach ($classes as $class)
        {
                $unique_classes[$class] = $class;
        }
        $data['class'] = implode(' ', $unique_classes);

        // add onclick js event if url is specified
        if (isset($data['url']))
        {
                if (empty($data['onclick']))
                {
                        $data['onclick'] = '';
                }
                $data['onclick'] .= 'window.location.href=BASE_URL+"'.$data['url'].'";';
                unset($data['url']);
        }

        // build all variables for the element
        unset($data['vars']);
        $vars = [];
        foreach ($data as $html_key => $html_value)
        {
                if (is_string($html_value) || is_numeric($html_value))
                {
                        $vars[] = $html_key.'="'.trim($html_value).'"';
                }
        }
        $vars = implode(' ', $vars);
        $data['vars'] = $vars;

        return $data;
}

function color($name, $data = [])
{
        if (post($name))
        {
                $rgb = post($name);
        }
        elseif (!empty($data['value']))
        {
                $rgb = $data['value'];
        }
        else
        {
                $rgb = 'transparent';
        }
        return '
                <div class="color" style="background-color:'.$rgb.';">
                        <input type="hidden" name="'.$name.'" id="'.$name.'" class="'.str_replace('_', '-', $name).'" value="'.$rgb.'" />
                </div>
        ';
}
