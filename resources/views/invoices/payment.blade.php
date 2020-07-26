<form method="post" action="{{ route('client.invoice.payment',[$currantWorkspace->slug,$invoice->id]) }}" class="require-validation"
      data-cc-on-file="false"
      data-stripe-publishable-key="{{ $currantWorkspace->stripe_key }}"
      id="payment-form">
    @csrf
    <div class="row">
        <div class="col-sm-8">
            <div class="custom-radio">
                <label class="font-16 font-weight-bold">{{__('Credit / Debit Card')}}</label>
            </div>
            <p class="mb-0 pt-1">{{__('Safe money transfer using your bank account. We support Mastercard, Visa, Discover and American express.')}}</p>
        </div>
        <div class="col-sm-4 text-sm-right mt-3 mt-sm-0">
            <img src="{{asset('assets/img/payments/master.png')}}" height="24" alt="master-card-img">
            <img src="{{asset('assets/img/payments/discover.png')}}" height="24" alt="discover-card-img">
            <img src="{{asset('assets/img/payments/visa.png')}}" height="24" alt="visa-card-img">
            <img src="{{asset('assets/img/payments/american express.png')}}" height="24" alt="american-express-card-img">
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="form-group">
                <label for="card-number">{{__('Card Number')}}</label>
                <input type="text" id="card-number" class="form-control required" placeholder="4242 4242 4242 4242" data-ismask="true" data-mask="0000 0000 0000 0000">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="card-name-on">{{__('Name on card')}}</label>
                <input type="text" name="name" id="card-name-on" class="form-control required" placeholder="{{\Auth::user()->name}}">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="card-expiry-date">{{__('Expiry date')}}</label>
                <input type="text" id="card-expiry-date" class="form-control required" placeholder="MM/YYYY" data-ismask="true" data-mask="00/0000">
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label for="card-cvv">{{__('CVV code')}}</label>
                <input type="text" id="card-cvv" class="form-control required" placeholder="123" data-ismask="true" data-mask="000">
            </div>
        </div>
    </div>
    <div class="row">
        <div class="form-group col-md-12">
            <label for="amount">{{__('Amount')}}</label>
            <input class="form-control" required="required" min="0" name="amount" type="number" value="{{$invoice->getDueAmount()}}" step="0.01" min="0" max="{{$invoice->getDueAmount()}}" id="amount">
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="error" style="display: none;">
                <div class='alert-danger alert'>{{__('Please correct the errors and try again.')}}</div>
            </div>
        </div>
    </div>
    <div class="form-group mt-3">
        <button class="btn btn-primary" type="submit">{{ __('Make Payment') }}</button>
    </div>
</form>

<script type="text/javascript" src="https://js.stripe.com/v2/"></script>

<script type="text/javascript">
    $(function() {
        var $form = $(".require-validation");
        $('form.require-validation').bind('submit', function(e) {
            var $form = $(".require-validation"),
                valid = true,
                $errorMessage = $form.find('div.error');
            $errorMessage.hide();

            $('.has-error').removeClass('has-error');
            // console.log($inputs);
            $form.find('.required').each(function(i, el) {
                var $input = $(el);
                if ($input.val() === '') {
                    $input.parent().addClass('has-error');
                    $errorMessage.show();
                    valid = false;
                }
            });
            if(!valid)
            {
                return false;
            }
            if (!$form.data('cc-on-file')) {
                e.preventDefault();
                $form.find('[type="submit"]').attr('disabled','disabled');
                Stripe.setPublishableKey($form.data('stripe-publishable-key'));
                var expiry = $("#card-expiry-date").val().split("/");
                Stripe.createToken({
                    number: $('#card-number').val(),
                    cvc: $('#card-cvc').val(),
                    exp_month: expiry[0],
                    exp_year: expiry[1]
                }, stripeResponseHandler);
            }
        });

        function stripeResponseHandler(status, response) {
            if (response.error) {
                toastr('Error',response.error.message,'error');
                $form.find('[type="submit"]').removeAttr('disabled');
            } else {
                // token contains id, last4, and card type
                var token = response['id'];
                // insert the token into the form so it gets submitted to the server
                $form.find('input[type=text]').not('#card-name-on').empty();
                $form.append("<input type='hidden' name='stripeToken' value='" + token + "'/>");
                $form.find('[type="submit"]').attr('disabled','disabled');
                $form.get(0).submit();
            }
        }

    });
</script>
