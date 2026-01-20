<footer class="footer fixed-bottom d-flex">
  <div class="app-info">
    MyG2024 App Info <i class="fa fa-info-circle" aria-hidden="true"></i>
  </div>
  <div class="copy-text">
    <i class="fa fa-copyright" aria-hidden="true"></i>
    2024-25 MyG Claim Application.
  </div>
  <div class="powered ml-auto">
    MYG-TravelClaim2024.V.0.1 by Exacore
  </div>
</footer>

<!-- Bootstrap core JavaScript
================================================== -->
<!-- Placed at the end of the document so the pages load faster -->
<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script>window.jQuery || document.write('<script src="js/vendor/jquery-slim.min.js"><\/script>')</script>
<script src="js/vendor/popper.min.js"></script>
<script src="js/bootstrap.min.js"></script>

<script src="js/jquery.fancybox.min.js"></script>

<script type="text/javascript" src="https://plugins.slyweb.ch/jquery-clock-timepicker/node_modules/jquery/dist/jquery.min.js"></script>
  <script type="text/javascript" src="js/jquery-clock-timepicker.js"></script>
  <script type="text/javascript">
    $(document).ready(function() {
    $('.standard').clockTimePicker();
    $('.required').clockTimePicker({required:true});
    $('.separatorTime').clockTimePicker({separator:'.'});
    $('.precisionTime5').clockTimePicker({precision:5});
    $('.precisionTime10').clockTimePicker({precision:10});
    $('.precisionTime15').clockTimePicker({precision:15});
    $('.precisionTime30').clockTimePicker({precision:30});
    $('.precisionTime60').clockTimePicker({precision:60});
    $('.simpleTime').clockTimePicker({onlyShowClockOnMobile:true});
    $('.duration').clockTimePicker({duration:true, maximum:'80:00'});
    $('.durationNegative').clockTimePicker({duration:true, durationNegative:true});
    $('.durationMinMax').clockTimePicker({duration:true, minimum:'1:00', maximum:'5:30'});
    $('.durationNegativeMinMax').clockTimePicker({duration:true, durationNegative:true, minimum:'-5:00', maximum:'5:00', precision:5});
  });
  </script>
</body>
</html>