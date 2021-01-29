( function( $ ) { 'use strict';

	$( document ).ready( function() {

		var MCI_ADMIN = {
			
			init: function() {
				this.initializeSessionData();
				this.getInvestmentsStats( this, 1, 1 );
				this.getUserMatchingNames( this );
				this.awardInvestmentByButton();
				this.initializeChart( this );
				this.addDownloadCSVbutton();
			},

			/**
			 * Add download CSV button
			 */
			addDownloadCSVbutton: function() {
				if( MciAdmin.isWithdrawalPost && $( '.actions.bulkactions' ).length > 0 ) {
					$( '.actions.bulkactions' ).append( '<button name="mci_dw" class="mci-dw-csv button">Download CSV</button>' );
				}
			},

			filterByBtn: function() {
				if( $( '.mci-filter-btn' ).length > 0 && $( '.mci-user-select' ).length > 0 ) {
					$( '.mci-filter-btn' ).on( 'click', function() {

						var data = {
							action: 'mci_get_investment_data_specific_user',
							user_id: $( '.mci-user-select' ).data( 'id' )
						};

						$.post( MciAdmin.ajaxURL, data, function( resp ) {

							var coin_balance = parseFloat( resp.coin_balance ).toFixed( 2 ),
								investments_earnings = parseFloat( resp.investments_earnings ).toFixed( 2 ),
								investments = parseFloat( resp.investments ).toFixed( 2 ),
								investments_referrals = parseFloat( resp.investments_referrals ).toFixed( 2 ),
								other_referrals = parseFloat( resp.other_referrals ).toFixed( 2 );

							$( '.mci-coin-bal' ).html( coin_balance );
							$( '.mci-total-earnings' ).html( investments_earnings );
							$( '.mci-total-investments' ).html( investments );
							$( '.mci-investment-referrals' ).html( investments_referrals );
							$( '.mci-other-referrals' ).html( other_referrals );

						}, 'json' );
					} );
				}
			},

			awardInvestmentByButton: function() {
				if( $( '.mci-award-investment' ).length > 0 ) {
					$( '.mci-award-investment' ).on( 'click', function( e ) {

						e.preventDefault();

						var data = {
							action: 'mci_award_investment_by_button',
							order_id: $( this ).data( 'id' )
						};
						var btn = $( this );

						btn.attr( 'disabled', 'true' );
						$.post( MciAdmin.ajaxURL, data, function( resp ) {
							if( resp.locked ) {
								btn.attr( 'disabled', 'true' );
								$( '.mci-info-box' ).html( 'All profits awarded.' );
								alert( 'All profits awarded.' );
							} else {
								alert( 'Profit Awarded.' );
								btn.removeAttr( 'disabled', 'true' );
							}
						}, 'json' );
					} );
				}
			},

			getSpecificUserData: function( self ) {
				if( $( '.mci-user-select' ).length > 0 ) {
					$( '.mci-user-select' ).on( 'click', function() {
						$( '.mci-suggestions' ).hide();
						var data = {
							action: 'mci_get_investment_data_specific_user',
							user_id: $( this ).data( 'id' )
						};

						$.post( MciAdmin.ajaxURL, data, function( resp ) {

							var coin_balance = parseFloat( resp.coin_balance ).toFixed( 2 ),
								investments_earnings = parseFloat( resp.investments_earnings ).toFixed( 2 ),
								investments = parseFloat( resp.investments ).toFixed( 2 ),
								investments_referrals = parseFloat( resp.investments_referrals ).toFixed( 2 ),
								other_referrals = parseFloat( resp.other_referrals ).toFixed( 2 );

							$( '.mci-coin-bal' ).html( coin_balance );
							$( '.mci-total-earnings' ).html( investments_earnings );
							$( '.mci-total-investments' ).html( investments );
							$( '.mci-investment-referrals' ).html( investments_referrals );
							$( '.mci-other-referrals' ).html( other_referrals );

						}, 'json' );
					} );
				}
			},

			getUserMatchingNames: function( self ) {

				$( '.mci_user' ).on( 'keyup', function() {
					if( $( this ).val().length < 1 ) {
						$( '.mci-suggestions' ).hide();
						return false;
					}

					var data = {
						action: 'mci_get_matching_user',
						username: $( this ).val()
					};

					$.post( MciAdmin.ajaxURL, data, function( resp ) {
						$( '.mci-suggestions' ).html( resp );
						$( '.mci-suggestions' ).show();
						self.getSpecificUserData( self );
						//self.filterByBtn();
					} );
				} );

				
			},

			initializeSessionData: function() {
				sessionStorage.setItem( 'coin_balance', 0 );
				sessionStorage.setItem( 'investments_referrals', 0 );
				sessionStorage.setItem( 'other_referrals', 0 );
				sessionStorage.setItem( 'investments', 0 );
				sessionStorage.setItem( 'investments_earnings', 0 );
				sessionStorage.setItem( 'total_invested', 0 );
			},

			getInvestmentsStats: function( self, req, req_str ) {

				req_str = req_str == undefined ? 1 : req_str;
				var data = {
					action: 'mci_get_investment_data',
					req: req_str
				};

				$.post( MciAdmin.ajaxURL, data, function( resp ) {

					if( req < Number( MciAdmin.ajaxReqs ) ) {
						req++;

						/**
						 * Save data on sessionStorage
						 */
						var coin_balance = sessionStorage.getItem( 'coin_balance' ) !== undefined && sessionStorage.getItem( 'coin_balance' ) !== null ? parseFloat( sessionStorage.getItem( 'coin_balance' ) ) + resp.inv_data.coin_balance : resp.inv_data.coin_balance;
						sessionStorage.setItem( 'coin_balance', coin_balance );

						var investments_referrals = sessionStorage.getItem( 'investments_referrals' ) !== undefined && sessionStorage.getItem( 'investments_referrals' ) !== null ? parseFloat( sessionStorage.getItem( 'investments_referrals' ) ) + resp.inv_data.investments_referrals : resp.inv_data.investments_referrals;
						sessionStorage.setItem( 'investments_referrals', investments_referrals );

						var other_referrals = sessionStorage.getItem( 'other_referrals' ) !== undefined && sessionStorage.getItem( 'other_referrals' ) !== null ? parseFloat( sessionStorage.getItem( 'other_referrals' ) ) + resp.inv_data.other_referrals : resp.inv_data.other_referrals;
						sessionStorage.setItem( 'other_referrals', other_referrals );

						var investments = sessionStorage.getItem( 'investments' ) !== undefined && sessionStorage.getItem( 'investments' ) !== null ? parseFloat( sessionStorage.getItem( 'investments' ) ) + resp.inv_data.investments : resp.inv_data.investments;
						sessionStorage.setItem( 'investments', investments );

						var investments_earnings = sessionStorage.getItem( 'investments_earnings' ) !== undefined && sessionStorage.getItem( 'investments_earnings' ) !== null ? parseFloat( sessionStorage.getItem( 'investments_earnings' ) ) + resp.inv_data.investments_earnings : resp.inv_data.investments_earnings;
						sessionStorage.setItem( 'investments_earnings', investments_earnings );

						var total_invested = sessionStorage.getItem( 'total_invested' ) !== undefined && sessionStorage.getItem( 'total_invested' ) !== null ? parseFloat( sessionStorage.getItem( 'total_invested' ) ) + resp.inv_data.total_invested : resp.inv_data.total_invested;
						sessionStorage.setItem( 'total_invested', total_invested );

						return self.getInvestmentsStats( self, req, resp.req_num );
					} 
					// console.log(resp.inv_data);
					if( Number( MciAdmin.ajaxReqs ) == 1 ) {
						/**
						 * Save data on sessionStorage
						 */
						var coin_balance = sessionStorage.getItem( 'coin_balance' ) !== undefined && sessionStorage.getItem( 'coin_balance' ) !== null ? parseFloat( sessionStorage.getItem( 'coin_balance' ) ) + resp.inv_data.coin_balance : resp.inv_data.coin_balance;
						sessionStorage.setItem( 'coin_balance', coin_balance );

						var investments_referrals = sessionStorage.getItem( 'investments_referrals' ) !== undefined && sessionStorage.getItem( 'investments_referrals' ) !== null ? parseFloat( sessionStorage.getItem( 'investments_referrals' ) ) + resp.inv_data.investments_referrals : resp.inv_data.investments_referrals;
						sessionStorage.setItem( 'investments_referrals', investments_referrals );

						var other_referrals = sessionStorage.getItem( 'other_referrals' ) !== undefined && sessionStorage.getItem( 'other_referrals' ) !== null ? parseFloat( sessionStorage.getItem( 'other_referrals' ) ) + resp.inv_data.other_referrals : resp.inv_data.other_referrals;
						sessionStorage.setItem( 'other_referrals', other_referrals );

						var investments = sessionStorage.getItem( 'investments' ) !== undefined && sessionStorage.getItem( 'investments' ) !== null ? parseFloat( sessionStorage.getItem( 'investments' ) ) + resp.inv_data.investments : resp.inv_data.investments;
						sessionStorage.setItem( 'investments', investments );

						var investments_earnings = sessionStorage.getItem( 'investments_earnings' ) !== undefined && sessionStorage.getItem( 'investments_earnings' ) !== null ? parseFloat( sessionStorage.getItem( 'investments_earnings' ) ) + resp.inv_data.investments_earnings : resp.inv_data.investments_earnings;
						sessionStorage.setItem( 'investments_earnings', investments_earnings );

						var total_invested = sessionStorage.getItem( 'total_invested' ) !== undefined && sessionStorage.getItem( 'total_invested' ) !== null ? parseFloat( sessionStorage.getItem( 'total_invested' ) ) + resp.inv_data.total_invested : resp.inv_data.total_invested;
						sessionStorage.setItem( 'total_invested', total_invested );
					}

					if( req >= Number( MciAdmin.ajaxReqs ) ) { 

						var coin_bal = parseFloat( sessionStorage.getItem( 'coin_balance' ) ).toFixed( 2 ),
							investments_earnings = parseFloat( sessionStorage.getItem( 'investments_earnings' ) ).toFixed( 2 ),
							investments = parseFloat( sessionStorage.getItem( 'investments' ) ).toFixed( 2 ),
							investments_referrals = parseFloat( sessionStorage.getItem( 'investments_referrals' ) ).toFixed( 2 ),
							other_referrals = parseFloat( sessionStorage.getItem( 'other_referrals' ) ).toFixed( 2 ),
							total_invested = parseFloat( sessionStorage.getItem( 'total_invested' ) ).toFixed( 2 );
							
						$( '.mci-coin-bal' ).html( coin_bal );
						$( '.mci-total-earnings' ).html( investments_earnings );
						$( '.mci-total-investments' ).html( investments );
						$( '.mci-investment-referrals' ).html( investments_referrals );
						$( '.mci-other-referrals' ).html( other_referrals );
						$( '.mci-total-invested' ).html( total_invested );

						// self.initializeChart( investments, investments_earnings, investments_referrals, other_referrals );
					}
				}, 'json' );

			},

			initializeChart: function( self ) {

				if( $( '.mci-earning-stats-chart' ).length <= 0 ) return false;
				var months = {
					'01' : 'enero', 
					'02' : 'febrero', 
					'03' : 'marzo', 
					'04' : 'abril', 
					'05' : 'Mayo', 
					'06' : 'junio', 
					'07' : 'julio', 
					'08' : 'agosto', 
					'09' : 'septiembre', 
					'10' : 'octubre', 
					'11' : 'noviembre', 
					'12' : 'diciembre',
					'-1' : 'septiembre',
					'-2' : 'octubre', 
					'-3' : 'noviembre', 
					'00' : 'diciembre'
				};
				var monthLabels = [];

				var logs = [],
					invested = [],
					investments = [],
					earnings = [],
					inv_refs = [],
					refs = []; 
				$.each( MciAdmin.chartData.sorted_months, function( key, elem ) {
					logs.push( MciAdmin.chartData.logs[elem] );
					monthLabels.push( months[elem] );
					investments.push( MciAdmin.chartData.investments[elem] );
					invested.push( MciAdmin.chartData.invested[elem] );
					earnings.push( MciAdmin.chartData.earnings[elem] );
					inv_refs.push( MciAdmin.chartData.investment_referrals[elem] );
					refs.push( MciAdmin.chartData.other_referrals[elem] );
				} );
				console.log(MciAdmin.chartData.other_referrals);
				$( '.mci-chart-loader' ).hide();
				var ctx = $( '.mci-earning-stats-chart' )[0];
				var myChart = new Chart( ctx, {
				    type: 'line',
				    data: {
				        datasets: [
				       		{
					            label: 'Total de monedas',
					            data: logs,
					            backgroundColor: [
					                'rgba(255, 99, 132, 0)',
					            ],
					            borderColor: [
					                '#8a2be2'
					            ],
					            borderWidth: 3,
					            lineTension: 0
					        },
					        {
					            label: 'Total invertido',
					            data: invested,
					            backgroundColor: [
					                'rgba(255, 99, 132, 0)'
					            ],
					            borderColor: [
					                '#5f9ea0',
					            ],
					            borderWidth: 3,
					            lineTension: 0
					        },
				        	{
					            label: 'Inversiones activas',
					            data: investments,
					            backgroundColor: [
					                'rgba(255, 99, 132, 0)',
					            ],
					            borderColor: [
					                '#ffa500'
					            ],
					            borderWidth: 3,
					            lineTension: 0
					        },
					        {
					            label: 'Ganancias',
					            data: earnings,
					            backgroundColor: [
					                'rgba(255, 99, 132, 0)'
					            ],
					            borderColor: [
					                '#a9a9a9'
					            ],
					            borderWidth: 3,
					            lineTension: 0
					        },/*
					        {
					            label: 'Referencias de inversiones',
					            data: inv_refs,
					            backgroundColor: [
					                'rgba(255, 99, 132, 0)'
					            ],
					            borderColor: [
					                '#03a9f4'
					            ],
					            borderWidth: 3,
					            lineTension: 0
					        },*/
					        {
					            label: 'referencias',
					            data: refs,
					            backgroundColor: [
					                'rgba(255, 99, 132, 0)'
					            ],
					            borderColor: [
					                '#9c546e',
					            ],
					            borderWidth: 3,
					            lineTension: 0
					        }
				        ],
				    	labels: monthLabels,
				    },
				    options: {
				        scales: {
				            yAxes: [{
				                ticks: {
				                    beginAtZero: true
				                },
				                // gridLines: false
				            }],

				        },
				        /*legend: {
				        	labels: false
				        }*/
				    }
				});
			}
		}

		MCI_ADMIN.init();
	} );

} )( jQuery );