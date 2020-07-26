<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.3.2/jspdf.min.js"></script>
<script>
    $(document).ready(function () {
        genScreenshot();
        function genScreenshot() {
            html2canvas(document.getElementById('boxes'), {
                onrendered: function (canvas) {
                    $('html').html("");
                    // $('#container').append(canvas);

                    var imgData = canvas.toDataURL("image/jpeg", 1.0);
                    var pdf = new jsPDF();
                    pdf.setProperties({
                        title: "{{Utility::invoiceNumberFormat($invoice->invoice_id)}}"
                    });
                    pdf.addImage(imgData, 'JPEG', 0, 0);
                    var string = pdf.output('datauristring');
                    var iframe = "<iframe width='100%' height='100%' src='" + string + "' frameborder='0'></iframe>"
                    document.write(iframe);
                    $('body').css('margin', '0');
                }
            });
        }
    });
</script>
