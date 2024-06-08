const express = require('express');
const stripe = require('stripe')('');//Key sk
const path = require('path');

const app = express();

app.use(express.json());

app.use(express.static('public'));

app.get('/', function(req, res) {
    res.sendFile(path.join(__dirname, 'public', 'index.html'));
    res.setHeader("Permissions-Policy", "attribution-reporting=(self)");
});

app.get('/cancel', function(req, res) {
  res.sendFile(path.join(__dirname, 'public', 'cancel.html'));
  res.setHeader("Permissions-Policy", "attribution-reporting=(self)");
});

app.get('/success', function(req, res) {
  res.sendFile(path.join(__dirname, 'public', 'success.html'));
  res.setHeader("Permissions-Policy", "attribution-reporting=(self)");
});


app.post('/payment-sheet', async (req, res) => {
  const { amount } = req.body;
  // Use an existing Customer ID if this is a returning customer.
    const customer = await stripe.customers.create({
  metadata: {
    order_id: '6735',
  },
});
  const ephemeralKey = await stripe.ephemeralKeys.create(
    {customer: customer.id},
    {apiVersion: '2024-04-10'}
  );
  const paymentIntent = await stripe.paymentIntents.create({
    amount: amount,
    currency: 'vnd',
    customer: customer.id,
    // In the latest version of the API, specifying the `automatic_payment_methods` parameter
    // is optional because Stripe enables its functionality by default.
    automatic_payment_methods: {
      enabled: true,
    },
  });

  res.json({
    paymentIntent: paymentIntent.client_secret,
    ephemeralKey: ephemeralKey.secret,
    customer: customer.id,
    publishableKey: '' //key pk
  });
});


app.post('/create-checkout-session', async (req, res) => {
  const id = req.body.id; 
  if (!id) {
      return res.status(400).send("Id order is required");
  }

  const price = req.body.price;
  if (!price) {
      return res.status(400).send("Price is required");
  }

  const session = await stripe.checkout.sessions.create({
      payment_method_types: ['card'],
      line_items: [{
          price_data: {
              currency: 'vnd',
              product_data: {
                  name: 'Payment',
              },
              unit_amount: price,
          },
          quantity: 1,
      }],
      mode: 'payment',
      success_url: `${req.headers.origin}/success?id=${id}&price=${price}`,
      cancel_url: `${req.headers.origin}/cancel?id=${id}&price=${price}`,
  });

  res.json({ sessionId: session.id });
});


const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`);
});
