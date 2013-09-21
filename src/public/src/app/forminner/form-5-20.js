define(function(require, exports, module){

	var _ = require( 'underscore' );
	var Backbone = require( 'backbone' );
	var Util = require( '/src/app/util' );

	var Layout = app.mission
	var list = Layout.list;
	var FormInner = require( '/src/app/forminner' );

	var Model = FormInner.Model.extend({
		urlRoot: '/mission/mission/id/', 
		defaults: {
			mission_type_id: 20
		}, 
		initialize: function(){
			this.defaults = _.extend( this.defaults_p, this.defaults )
			this.set( this.defaults )
			this.initialize_p();
		}
	});
	
	var FormView = FormInner.View.extend({
		el: '#forminner-mission-5-20', 
		template: _.template($('#template-formview-5-20').html()),
		template_close: _.template($('#template-formview-closed-5-20').html()),
		events: {
		
		}, 
		initialize: function(opt){

			this.events = _.extend( this.events_p, this.events );

			this.listenTo( opt.model, 'change', this.render )

			console.log()
			this.initialize_p( opt )
		}, 
		render: function(){

			var view = this;

			if( ( app_data.user.role_id == 3 || app_data.user.role_id == 4 ) || this.model.get( 'user_state' ) == 4 || this.model.get( 'state' ) == 2 ){

				// 已关闭
				this.$('#forminner-view').html( this.template_close( _.extend( this.model.toJSON(), {} ) ));
				
			}else{

				this.$('#forminner-view').html(this.template( _.extend( this.model.toJSON(), {} ) ));

				if( undefined != this.model.get('send_to_product_list') ){
					_.each( this.model.get('send_to_product_list'), function( one ){
						view.add_send_to_product_do( one.category_id, one.product_id, one.cnt, one.category, one.product, one.state, one.state_name, _.uniqueId(), one.id )
					} )
				}

				if( this.$('.datepicker').length > 0 ){
					this.$('.datepicker').val( this.model.get( 'pay_time' ) ).datepicker({
						language: 'zh-CN', 
						format: 'yyyy-mm-dd'
					})
				}

				this.resetRefundment()

			}

			if( null != Layout.form_view.form_board ){
				Layout.form_view.form_board.show();
			}

			if( null != Layout.form_view.comment ){
				Layout.form_view.comment.show();
			}
		}
	});
	
	module.exports = {
		Model: Model, 
		View: FormView 
	}
});