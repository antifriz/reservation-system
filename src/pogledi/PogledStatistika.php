<?php

defined('DOCUMENT_ROOT') or define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT']);

require_once DOCUMENT_ROOT . "/pogledi/Pogled.php";

require_once DOCUMENT_ROOT . "/sastavnice/Session.php";
require_once DOCUMENT_ROOT . "/podaci/korisnici/Ugostitelj.php";


/**
 * Class PogledStatistika
 */
class PogledStatistika extends Pogled
{

    /**
     * @var
     */
    private $ugostitelj;
    /**
     * @var
     */
    private $trenutna_statistika;
    /**
     * @var array
     */
    private $u_vremenu_statistika;

    /**
     * @param Ugostitelj $ugostitelj
     */
    function __construct($ugostitelj)
    {
        $this->ugostitelj = $ugostitelj;
        $this->trenutna_statistika = DBfacade::receiveAll(
            "CALL ugostitelj__dohvati_statistiku_trenutna(:id)",
            ['id' => $ugostitelj->getId()]
        )[0];
        $this->u_vremenu_statistika = DBfacade::receiveAll(
            "CALL ugostitelj__dohvati_statistiku_u_vremenu(:id)",
            ['id' => $ugostitelj->getId()]
        );

    }

    public function generiraj()
    {

        ?>

        <div class="container">
            <div class="page-header text-center">
                <h1>
                    Statistika ugostiteljskog objekta
                </h1>
            </div>
            <div class="row" style="overflow: hidden">
                <div class="col-xs-4">
                    <div id="tjedni-profit" style="margin-top: -47%; overflow: hidden"></div>
                </div>
                <div class="col-xs-4">
                    <div id="tjedni-prihod" style="margin-top: -47%; overflow: hidden"></div>

                </div>
                <div class="col-xs-4">
                    <div id="tjedni-rashod" style="margin-top: -47%; overflow: hidden"></div>

                </div>
            </div>
            <div class="row">
                <div class="thumbnail">
                    <div class="row">
                        <div class="col-xs-6">
                            <div id="broj-rezervacija" style="padding: 0 20px 0 0"></div>
                        </div>
                        <div class="col-xs-6">

                            <div id="prihod-rezervacija" style="padding: 0 20px 0 0"></div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    <?php

    }

    public function generirajJSdodatno()
    {
        ?>
        <script src="/js/highcharts.js"></script>
        <script src="/js/highcharts-more.js"></script>
        <script src="/js/solid-gauge.js"></script>
        <script type="text/javascript">
            $(function () {
                $('#broj-rezervacija').highcharts({
                    chart: {
                        type: 'spline',
                        zoomType: 'x'
                    },
                    title: {
                        text: 'Broj rezervacija u vremenu'
                    },
                    subtitle: {
                        text: ''
                    },
                    xAxis: {
                        type: 'datetime',
                        dateTimeLabelFormats: {

                            month: '%m.%Y.',
                            year: '%Y.'
                        },
                        title: {
                            text: 'Vrijeme'
                        },
                        minRange: 10 * 24 * 3600000 // seven days
                    },
                    yAxis: {
                        title: {
                            text: 'Broj rezervacija'

                        },
                        labels: {
                            step: 1

                        },
                        min: 0
                    },
                    tooltip: {
                        headerFormat: '<b>{series.name}</b><br>',
                        pointFormat: '{point.x:%d.%m.%Y.}: {point.y:.0f} rezervacije'
                    },

                    series: [{
                        name: 'Rezervacije',
                        data: [
                            <?php
                            $rows = DBfacade::receiveAll("SELECT
  unix_timestamp(date(rezervacija.vremenska_oznaka)) * 1000 AS vrijeme,
  count(rezervacija.id_rezervacija)                         AS vrijednost
FROM rezervacija
  where id_ugostitelj = :id
GROUP BY date(rezervacija.vremenska_oznaka); ",[ 'id' => $this->ugostitelj->getId()]);
                            foreach($rows as $row):
                            ?>
                            [<?= $row['vrijeme'] ?>, <?= $row['vrijednost'] ?>],
                            <?php endforeach; ?>

                        ]
                    }]
                });
                $('#prihod-rezervacija').highcharts({
                    chart: {
                        type: 'area',
                        zoomType: 'x'

                    },
                    title: {
                        text: 'Očekivani prihodi i rashodi u vremenu'
                    },
                    subtitle: {
                        text: ''
                    },
                    xAxis: {
                        type: 'datetime',
                        dateTimeLabelFormats: {

                            month: '%m.%Y.',
                            year: '%Y.'
                        },
                        title: {
                            text: 'Vrijeme'
                        },
                        minRange: 5 * 24 * 3600000 // seven days
                    },
                    yAxis: {
                        title: {
                            text: 'Vrijednost u kunama'

                        },
                        labels: {
                            step: 1

                        },
                        min: 0
                    },
                    tooltip: {
                        headerFormat: '<b>{series.name}</b><br>',
                        pointFormat: '{point.x:%d.%m.%Y.}: {point.y:.0f} kn'
                    },
                    plotOptions: {
                        area: {
                            /*
                             fillColor: {
                             linearGradient: { x1: 0, y1: 0, x2: 0, y2: 1},
                             stops: [
                             [0, Highcharts.getOptions().colors[0]],
                             [1, Highcharts.Color(Highcharts.getOptions().colors[0]).setOpacity(0).get('rgba')]
                             ]
                             },*/
                            <?php $dateStart = $this->u_vremenu_statistika[0] ?>
                            pointStart: Date.UTC(<?= $dateStart['godina'].','.$dateStart['mjesec'] .','.$dateStart['dan'] ?>),
                            marker: {
                                radius: 2
                            }/*,
                             lineWidth: 1,
                             states: {
                             hover: {
                             lineWidth: 1
                             }
                             },
                             threshold: null*/
                        }
                    },

                    series: [{
                        name: 'Očekivani prihod rezervacija u vremenu',
                        type: 'area',
                        pointInterval: 24 * 3600 * 1000,

                        data: [
                            <?php
                            $rows = $this->u_vremenu_statistika;
                            foreach($rows as $row)
                                echo $row['prihod'].',';
                                 echo 0;?>

                        ]
                    },
                        {
                            name: 'Očekivani rashod nabava u vremenu',
                            type: 'area',
                            pointInterval: 24 * 3600 * 1000,
                            data: [
                                <?php
                                $rows = $this->u_vremenu_statistika;
                                foreach($rows as $row)
                                    echo $row['rashod'].','; ?>

                            ]
                        }]
                });
                var gaugeOptions = {

                    chart: {
                        type: 'solidgauge'
                    },

                    title: null,

                    pane: {
                        center: ['50%', '90%'],
                        size: '100%',
                        startAngle: -90,
                        endAngle: 90,
                        background: {
                            backgroundColor: (Highcharts.theme && Highcharts.theme.background2) || '#EEE',
                            innerRadius: '60%',
                            outerRadius: '100%',
                            shape: 'arc'
                        }
                    },

                    tooltip: {
                        enabled: false
                    },

                    // the value axis
                    yAxis: {
                        stops: [
                            [0.1, '#DF5353'], // red
                            [0.5, '#DDDF0D'], // yellow
                            [0.9, '#55BF3B'] //  green
                        ],
                        lineWidth: 0,
                        minorTickInterval: null,
                        title: {
                            y: 20
                        },
                        labels: {
                            y: 20
                        }
                    },

                    plotOptions: {
                        solidgauge: {
                            dataLabels: {
                                y: 5,
                                borderWidth: 0,
                                useHTML: true
                            }, animation: {duration: 0, easing: 'swing'}


                        }
                    }
                };

                <?php
                    $profitRangeMin = min($this->trenutna_statistika['tjedni_profit']*2,$this->trenutna_statistika['profit']*1.05,0);
                    $profitRangeMax = max($this->trenutna_statistika['tjedni_profit']*2,$this->trenutna_statistika['profit']*1.05,0);
                    $profitRange = max($profitRangeMax,-$profitRangeMin);
                ?>
                // The speed gauge
                $('#tjedni-profit').highcharts(Highcharts.merge(gaugeOptions, {
                    yAxis: {
                        min: <?= -$profitRange ?>,
                        max: <?= $profitRange ?>,
                        title: {
                            text: 'Tjedni profit'
                        }
                    },

                    credits: {
                        enabled: false
                    },

                    series: [{
                        name: 'Tjedni profit',
                        data: [<?= min($this->trenutna_statistika['tjedni_profit']*2,$this->trenutna_statistika['profit']*1.05,0) ?>],
                        dataLabels: {
                            format: '<div style="text-align:center"><span style="font-size:25px;color:' +
                            ((Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black') + '">{y}</span><br/>' +
                            '<span style="font-size:12px;color:silver">HRK</span></div>'
                        },
                        tooltip: {
                            valueSuffix: ' km/h'
                        }
                    }]

                }));

                // The speed gauge
                $('#tjedni-prihod').highcharts(Highcharts.merge(gaugeOptions, {
                    yAxis: {
                        min: 0,
                        max: <?= max($this->trenutna_statistika['tjedni_prihod']*2,$this->trenutna_statistika['prihod']*1.05) ?>,
                        title: {
                            text: 'Tjedni prihod'
                        }
                    },

                    credits: {
                        enabled: false
                    },

                    series: [{
                        name: 'Tjedni prihod',
                        data: [0],
                        dataLabels: {
                            format: '<div style="text-align:center"><span style="font-size:25px;color:' +
                            ((Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black') + '">{y}</span><br/>' +
                            '<span style="font-size:12px;color:silver">HRK</span></div>'
                        },
                        tooltip: {
                            valueSuffix: ' km/h'
                        }
                    }]

                }));
                // The speed gauge
                $('#tjedni-rashod').highcharts(Highcharts.merge(gaugeOptions, {
                    yAxis: {
                        min: 0,
                        max: <?= max($this->trenutna_statistika['tjedni_rashod']*2,$this->trenutna_statistika['rashod']*1.05) ?>,
                        title: {
                            text: 'Tjedni rashod'
                        },
                        stops: [
                            [0.1, '#55BF3B'], // red
                            [0.5, '#DDDF0D'], // yellow
                            [0.9, '#DF5353'] //  green
                        ]
                    },

                    credits: {
                        enabled: false
                    },

                    series: [{
                        name: 'Speed',
                        data: [0],
                        dataLabels: {
                            format: '<div style="text-align:center"><span style="font-size:25px;color:' +
                            ((Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black') + '">{y}</span><br/>' +
                            '<span style="font-size:12px;color:silver">HRK</span></div>'
                        },
                        tooltip: {
                            valueSuffix: 'kuna'
                        }
                    }]

                }));


                var profit = $('#tjedni-profit').highcharts().series[0].points[0];
                var prihod = $('#tjedni-prihod').highcharts().series[0].points[0];
                var rashod = $('#tjedni-rashod').highcharts().series[0].points[0];
                setTimeout(function () {
                    profit.update(<?= $this->trenutna_statistika['profit'] ?>)
                }, 100);

                setTimeout(function () {
                    prihod.update(<?= $this->trenutna_statistika['prihod'] ?>)
                }, 100);
                setTimeout(function () {
                    rashod.update(<?= $this->trenutna_statistika['rashod'] ?>)
                }, 100);


            });
        </script>
    <?php
    }
}

