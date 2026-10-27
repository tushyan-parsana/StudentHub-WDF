<?php
declare(strict_types=1);

function writeAuditLog(
    mysqli $mysqli,
    string $action,
    string $entityType,
    string $entityId,
    array $details = []
): void {
    $actorId = (string) ($_SESSION['student_id'] ?? 'unknown');
    $detailsJson = json_encode($details, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    $statement = $mysqli->prepare(
        'INSERT INTO audit_logs
         (actor_student_id, action, entity_type, entity_id, details)
         VALUES (?, ?, ?, ?, ?)'
    );
    $statement->bind_param(
        'sssss',
        $actorId,
        $action,
        $entityType,
        $entityId,
        $detailsJson
    );
    $statement->execute();
}
