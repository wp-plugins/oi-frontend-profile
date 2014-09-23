<?
if( !function_exists('oinput') )
{

function oinput($atts)
{
	extract(shortcode_atts(array(
		'key'			=> '',		// name of element ID and NAME
		'type'			=> 'text',	// field type: text, hidden, password, select, option, textarea
		'value'			=> '',		// field value
		'before'		=> null,	// label befor field
		'placeholder'	=> '',		// text example in a field
		'after'			=> null,	// label after field
		'hint'			=> '',		// hint text after field
		'class'			=> '',		// element class 
		'style'			=> '',		// element style 
		'checked'		=> false,	// readonly flag
		'readonly'		=> false,	// readonly flag
		'disabled'		=> false,	// disabled flag
		'required'		=> false,	// required flag
		'addon'			=> '',		// you able to write what ever you want to see inside a field
		'delimiter'		=> '',		// you able to separate label and input with some tag
	), $atts));
	if($key)
	{
		if($before){$before = '<label for="'.$key.'">'.$before.'</label>';}
		if($placeholder){$placeholder = ' placeholder="'.$placeholder.'"';}
		if($after){$after = '<label for="'.$key.'">'.$after.'</label>';}
		if($hint){$hint = '<span class="help-block description">'.$hint.'</span>';}
		if($class){$class = ' '.$class;}
		if($style){$style = ' style="'.$style.'"';}
		if($checked==true){$checked = ' checked';}else{$checked = '';}
		if($readonly==true){$readonly = ' readonly';}else{$readonly = '';}
		if($disabled==true){$disabled = ' disabled';}else{$disabled = '';}
		if($required==true){$required = ' required';}else{$required = '';}
		$addon = $placeholder.$style.$checked.$readonly.$disabled.$required.' '.$addon;
		switch ($type) {
			case 'select':
				$out = 
				'<select class="form-control'.$class.'" id="'.$key.'" name="'.$key.'"'.$addon.'>'.
					$value.
				'</select>';
				break;
			case 'option':
				$out = '';
				foreach ( $key as $k => $v )
				{
					$out .= '<option value="'.$k.'" '.selected($value,$k,false).'>'.$v.'</option>';
				}
				break;
			case 'hidden':
				$out = '<input type="'.$type.'" id="'.$key.'" name="'.$key.'" value="'.$value.'" />';
			break;
			case 'radio':
				if( $value ){$addon .= ' checked';}
				$out = '<input class="'.$class.'" type="'.$type.'" id="'.$key.'" name="'.$key.'"'.' value="1"'.$addon.' />';
			break;
			case 'checkbox':
				if( $value ){$addon .= ' checked';}
				$out = '<input class="'.$class.'" type="'.$type.'" id="'.$key.'" name="'.$key.'"'.' value="1"'.$addon.' />';
			break;
			case 'textarea':
				$out = '<textarea class="form-control'.$class.'" id="'.$key.'" name="'.$key.'" '.$addon.'>'.$value.'</textarea>';
			break;
			default:
				$out = '<input class="form-control'.$class.'" type="'.$type.'" id="'.$key.'" name="'.$key.'" value="'.$value.'"'.$addon.' />';
			break;
		}
		
		if($delimiter<>'')
		{
			$sd = '<'.$delimiter.'>';
			$ed = '</'.$delimiter.'>';
		}
		$out = $sd.$before.$ed.$sd.$out.$after.$hint.$ed;	
		return $out;
	}
}
add_shortcode('oinput','oinput');
}
?>