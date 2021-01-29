( function( $ ) { 'use strict';

	$( document ).ready( function() {

		var MCI = {
			
			init: function() {
				this.initializeChart();
				this.hideEmptyNotifications();
			},

			hideEmptyNotifications: function() {

				// Select the node that will be observed for mutations
				const targetNode = document.querySelector('#header-notifications-dropdown-elem ul.notification-list');
				
				// Options for the observer (which mutations to observe)
				const config = { attributes: true, childList: true, subtree: true };

				// Callback function to execute when mutations are observed
				const callback = function(mutationsList, observer) {
				    // Use traditional 'for loops' for IE 11
				    for(const mutation of mutationsList) {
				        if( mutation.type === 'childList' && $( mutation.target ).hasClass( 'notification-list' ) ) {
				        	
				        	if( $( 'li.read-item' ).length > 0 ) {
				        		$.each( $( 'li.read-item' ), function( index, elemLI ) { 
									if( $( elemLI ).find( '.notification-content .bb-full-link a' ).attr( 'href' ) == '' || ! $( elemLI ).find( '.notification-content .bb-full-link a' ).attr( 'href' ) || $( elemLI ).find( '.notification-content .bb-full-link a' ).attr( 'href' ) == 'undefined' ) {
											
										$( elemLI ).hide();
									}
								} );
				        	}
				        }
				    }
				};

				// Create an observer instance linked to the callback function
				const observer = new MutationObserver(callback);

				// Start observing the target node for configured mutations
				observer.observe(targetNode, config);
			},

			/**
			 * Initialize Earning Chart
			 */
			initializeChart: function() {

				if( $( '.mci-chart-earning' ).length <= 0 ) return false;

				var ctx = $( '.mci-chart-earning' )[0],
					og_investments = $( '.mci-chart-earning' ).data( 'act-inv' ).split(','),
					earnings = $( '.mci-chart-earning' ).data( 'earning' ).split(','),
					inv_refs = $( '.mci-chart-earning' ).data( 'inv-ref' ).split(','),
					ot_ref = $( '.mci-chart-earning' ).data( 'ot-ref' ).split(','),
					logs = $( '.mci-chart-earning' ).data( 'bal' ).split(','),
					invested = $( '.mci-chart-earning' ).data( 'invested' ).split(','),
					months1 = $( '.mci-chart-earning' ).data( 'months' ).split(',');
					

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
				$.each( months1, function( key, elem ) {
					monthLabels.push( months[elem] );

				} );

				var myChart = new Chart( ctx, {
				    type: 'line',
				    data: {
				         datasets: [
				       		/*{
					            label: 'Total de monedas',
					            data: logs.reverse(),
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
					            data: invested.reverse(),
					            backgroundColor: [
					                'rgba(255, 99, 132, 0)',
					            ],
					            borderColor: [
					                '#5f9ea0',
					            ],
					            borderWidth: 3,
					            lineTension: 0
					        },*/
				        	{
					            label: 'Inversiones activas',
					            data: og_investments,
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
					        },
					        /*{
					            label: 'Referencias de inversiones',
					            data: inv_refs.reverse(),
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
					            data: ot_ref,
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
				                }
				            }]
				        },
				        legend: {
				        	// labels: false
				        }
				    }
				});
			}
		}

		MCI.init();
	} );

} )( jQuery );