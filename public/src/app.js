define(function(require, exports, module) {

	require('/src/json2/json2')
	require('underscore')
	require('backbone')
	
	var Test = require('/src/test');

	var test = new Test();
	test.test();
});