
    <div class="footer">
    <p>&copy; Matthieu Guffroy - 2014 - <a href="admin">Administration</a></p>
    </div>

    </div> <!-- /container -->


    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <!-- Latest compiled and minified JavaScript -->
    <script src="//code.jquery.com/jquery-1.11.0.min.js"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <script src="static/jquery.plugin.js"></script>
    <script src="static/jquery.countdown.js"></script>
    <script>
    (function($) {
    $.countdown.regionalOptions['fr'] = {
        labels: ['Années', 'Mois', 'Semaines', 'Jours', 'Heures', 'Minutes', 'Secondes'],
        labels1: ['Année', 'Mois', 'Semaine', 'Jour', 'Heure', 'Minute', 'Seconde'],
        compactLabels: ['a', 'm', 's', 'j'],
        whichLabels: function(amount) {
            return (amount > 1 ? 0 : 1);
        },
        digits: ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
        timeSeparator: ':', isRTL: false};
    $.countdown.setDefaults($.countdown.regionalOptions['fr']);


    $('#Countdown1').countdown({until: c1, layout: '<b>{dn} {dl} {hnn}{sep}{mnn}{sep}{snn}</b> {desc}' });
    $('#Countdown2').countdown({until: c2, layout: '<b>{dn} {dl} {hnn}{sep}{mnn}{sep}{snn}</b> {desc}' });
    $('#Countdown3').countdown({until: c3, layout: '<b>{dn} {dl} {hnn}{sep}{mnn}{sep}{snn}</b> {desc}' });
    $('#Countdown4').countdown({until: c4, layout: '<b>{dn} {dl} {hnn}{sep}{mnn}{sep}{snn}</b> {desc}' });
    $('#Countdown5').countdown({until: c5, layout: '<b>{dn} {dl} {hnn}{sep}{mnn}{sep}{snn}</b> {desc}' });
    $('#Countdown6').countdown({until: c6, layout: '<b>{dn} {dl} {hnn}{sep}{mnn}{sep}{snn}</b> {desc}' });

})(jQuery);
    </script>
    </body>
</html>
