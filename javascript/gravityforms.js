/* global clipchamp_gravityforms_strings, gform */

gform.addFilter( 'gform_form_editor_can_field_be_added', function( result, formulaField, formId, calcObj ){
	if ( 'clipchamp' === formulaField && GetFieldsByType( [ 'clipchamp' ] ).length > 0 ) {
		alert( clipchamp_gravityforms_strings.onlyOneField );
		return false;
	}
	return true;
});
