<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_auth();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Metode tidak diizinkan.'); }
require_valid_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) { http_response_code(400); exit('ID pengeluaran tidak valid.'); }
$statement = db()->prepare('DELETE FROM expenses WHERE id = ? AND user_id = ?');
$statement->execute([$id, current_user_id()]);
redirect($statement->rowCount() === 1 ? 'expenses/index.php?notice=expense-deleted' : 'expenses/index.php');
