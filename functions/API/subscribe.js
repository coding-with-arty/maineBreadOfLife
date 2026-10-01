export async function onRequestPost({ request, env }) {
  try {
    const formData = await request.formData();
    const email = formData.get('email');
    const name = formData.get('name');

    if (!email) {
      return new Response(JSON.stringify({ error: 'Email is required' }), { status: 400 });
    }

    // Example: Forwarding to MailerLite / Mailchimp API
    const response = await fetch('https://connect.mailerlite.com/api/subscribers', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${env.MAILERLITE_API_KEY}`
      },
      body: JSON.stringify({
        email: email,
        fields: { name: name }
      })
    });

    if (response.ok) {
      return new Response("OK", { status: 200 });
    } else {
      return new Response("Failed to subscribe.", { status: 400 });
    }
  } catch (err) {
    return new Response(err.message, { status: 500 });
  }
}
