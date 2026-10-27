<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'config.php';

$connectionMessage = '';
$queryMessage = '';
$event = null;

try {
    $connectionCheck = $conn->query('SELECT 1');
    if ((int) $connectionCheck->fetchColumn() !== 1) {
        throw new RuntimeException('The database health check returned an unexpected result.');
    }

    $connectionMessage = 'PDO connection successful. Connected to the StudentHub database.';

    $statement = $conn->prepare(
        'SELECT event_id, event_title, event_organiser, event_date, event_fees
         FROM events
         WHERE event_id = :event_id
         LIMIT 1'
    );
    $statement->execute([':event_id' => 1]);
    $event = $statement->fetch();
    $queryMessage = 'Prepared statement executed successfully.';
} catch (PDOException | RuntimeException $exception) {
    http_response_code(500);
    $connectionMessage = 'PDO connection failed.';
    $queryMessage = 'Database error: ' . $exception->getMessage();
    error_log('PDO connectivity test failed: ' . $exception->getMessage());
}

function h(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDO Connectivity Test | StudentHub</title>
    <style>
        body { margin: 0; padding: 2rem; background: #f5f7fb; color: #1f2937; font: 16px Arial, sans-serif; }
        main { max-width: 720px; margin: auto; padding: 2rem; border-radius: 12px; background: #fff; box-shadow: 0 4px 16px #0001; }
        .success { color: #166534; }
        .error { color: #b91c1c; }
        code { padding: .15rem .35rem; background: #f3f4f6; }
        dt { margin-top: 1rem; font-weight: bold; }
        dd { margin: .35rem 0 0; }
    </style>
</head>
<body>
    <main>
        <h1>Practical 8: PDO Connectivity</h1>
        <h2 class="<?= str_contains($connectionMessage, 'successful') ? 'success' : 'error' ?>">
            <?= h($connectionMessage) ?>
        </h2>
        <p><?= h($queryMessage) ?></p>
        <?php if ($event): ?>
            <h2>Prepared statement result</h2>
            <dl>
                <dt>Event ID</dt>
                <dd><?= h((string) $event['event_id']) ?></dd>
                <dt>Event title</dt>
                <dd><?= h((string) $event['event_title']) ?></dd>
                <dt>Organiser</dt>
                <dd><?= h((string) $event['event_organiser']) ?></dd>
                <dt>Date</dt>
                <dd><?= h((string) $event['event_date']) ?></dd>
                <dt>Fees</dt>
                <dd>₹<?= h((string) $event['event_fees']) ?></dd>
            </dl>
        <?php endif; ?>
    </main>
</body>
</html>
