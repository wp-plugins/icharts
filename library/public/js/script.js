function v30v (data){
	this.data = jQuery.extend(v30v_data,data);
};

v30v.prototype.log = function(value) {
	if(this.data.dodebug==1)
	{
		console.log(value);
	}
}
v30v.prototype.json = function(url,data,success) {
	jQuery.ajax({
			type:'POST',
			url:url,
			data:data,
			success:success,
			dataType:'json'
	});
}
v30v.prototype.void = function(value,def_value) {
	var retval =  '';
	if(typeof(def_value) == 'undefined')
	{
		retval =  typeof(value) == 'undefined';
	}
	else
	{
		if(typeof(value) == 'undefined')
		{
			retval = def_value;
		}
		else
		{
			retval = value;
		}
	}
	return retval;
}
v30v.prototype.entitydecode = function(encodedStr) {
	return jQuery("<div/>").html(encodedStr).text();
}