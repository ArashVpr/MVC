<?php require APPROOT . '/views/bases/header.php'; ?>

<h1>Contact Us</h1>
<form action="/contact/submit" method="post">
    <label for="name">Name:</label>
    <input type="text" id="name" name="name" required><br>

    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required><br>

    <label for="message">Message:</label>
    <textarea id="message" name="message" required></textarea><br>

    <button type="submit">Send</button>
</form>

<?php require APPROOT . '/views/bases/footer.php'; ?>