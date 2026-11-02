<?php
function count_table(mysqli $conn, string $table): int {
    if(in_array($table, ['members', 'trainers', 'training_plans'], true)) {
        $sql = "SELECT COUNT(*) as number FROM $table";
        $result = $conn->query($sql);

        return (int)$result->fetch_row()[0];
    }
    
    return throw new InvalidArgumentException("Table not allowed: $table");
}

function validate_member(array $input): array {
    $errors = [];
    $data = [];

    $data['first_name'] = trim($input['first_name'] ?? '');
    $data['last_name'] = trim($input['last_name'] ?? '');
    $data['email'] = trim($input['email'] ?? '');
    $data['phone_number'] = trim($input['phone_number'] ?? '');
    $data['trainer_id'] = (int)($input['trainer_id'] ?? '');
    $data['plan_id'] = trim( $input['plan_id'] ?? '');

    if($data['first_name'] === '') {
        $errors[] = 'First name is required.';
    }

    if($data['last_name'] === '') {
        $errors[] = 'Last name is required.';
    }

    if($data['email'] === '') {
        $errors[] = 'Email is required.';
    } else if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email.'; 
    }

    if($data['phone_number'] === '') {
        $data['phone_number'] = NULL;
    }

    if(!$data['trainer_id']) {
        $data['trainer_id'] = NULL;
    }

    if ($data['plan_id'] === '') {                         
        $errors[] = 'Training plan is required.';
    } else {
        $data['plan_id'] = filter_var($data['plan_id'], FILTER_VALIDATE_INT); 
        if ($data['plan_id'] === false) {
            $errors[] = 'Invalid training plan.';
        }
    }

    return ['data' => $data, 'errors' => $errors];
}

function validate_photo(array $input): array {
    $errors = [];
    $data = [];
    $extension = NULL;

    $data['photo'] = $input['photo'] ?? NULL;

    if($data['photo'] === NULL || $data['photo']['error'] === UPLOAD_ERR_NO_FILE) {
        $data['photo'] = NULL;
    }

    if($data['photo'] !== NULL) {
        if($data['photo']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Photo upload failed.';
        } else if($data['photo']['size'] > 2 * 1024 * 1024) {
            $errors[] = 'Photo must be 2 MB or smaller.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $actual_mime_type = $finfo->file($data['photo']['tmp_name']); 

            $allowed_types = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp'
            ];
    
            if(!isset($allowed_types[$actual_mime_type])) {
                $errors[] = 'Photo must be JPG, PNG or WEBP.';
            } else {
                $extension = $allowed_types[$actual_mime_type];
            }
        }
    }

    return ['errors' => $errors, 'data' => $data, 'extension' => $extension];
}

function render_access_card(array $member): string {
    $full_name = htmlspecialchars($member['first_name'] . ' ' . $member['last_name']);
    $plan = htmlspecialchars($member['plan_name'] ?? '—');
    $trainer = htmlspecialchars($member['trainer_name'] ?? 'No trainer');
    $card_no = str_pad((string)$member['member_id'], 6, '0', STR_PAD_LEFT);
    $member_since = date('d.m.Y.', strtotime($member['created_at']));

    $photo_html = '<table class="photo-placeholder"><tr><td>'
        . htmlspecialchars(mb_substr($member['first_name'], 0, 1) . mb_substr($member['last_name'], 0, 1))
        . '</td></tr></table>';

    if (!empty($member['photo_path']) && file_exists($member['photo_path'])) {
        $mime = mime_content_type($member['photo_path']);
        $base64 = base64_encode(file_get_contents($member['photo_path']));
        $photo_html = '<img class="photo" src="data:' . $mime . ';base64,' . $base64 . '">';
    }

    $html = <<<HTML
    <!DOCTYPE html>
    <html>
    <head>
    <meta charset="UTF-8">
    <style>
        @page { margin: 0; }

        body {
            margin: 0;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 7pt;
            color: #1f2937;
        }

        .card {
            width: 243pt;
            height: 153pt;
            position: relative;
            background: #ffffff;
        }

        .header {
            background: #111827;
            color: #ffffff;
            padding: 8pt 10pt;
            height: 18pt;
        }

        .brand {
            font-size: 10pt;
            font-weight: bold;
            letter-spacing: 1pt;
        }

        .label-top {
            font-size: 6pt;
            color: #9ca3af;
            letter-spacing: 1pt;
        }

        .accent {
            height: 3pt;
            background: #22c55e;
        }

        .content {
            padding: 8pt 10pt 0 10pt;
        }

        .photo,
        .photo-placeholder {
            width: 50pt;
            height: 50pt;
            border-radius: 4pt;
        }

        .photo-placeholder {
            background: #e5e7eb;
        }

        .photo-placeholder td {
            height: 50pt;
            width: 50pt;
            color: #6b7280;
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
        }

        .name {
            font-size: 10pt;
            font-weight: bold;
            margin-bottom: 4pt;
        }

        .row-label {
            color: #6b7280;
            font-size: 6pt;
            text-transform: uppercase;
            letter-spacing: 0.5pt;
            padding-right: 6pt;
        }

        .row-value {
            font-size: 7pt;
        }

        .footer {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 5pt 10pt;
            border-top: 1pt solid #e5e7eb;
            font-size: 6pt;
            color: #6b7280;
        }

        .card-no {
            font-family: "DejaVu Sans Mono", monospace;
            font-size: 8pt;
            color: #111827;
            letter-spacing: 1pt;
        }

        table { border-collapse: collapse; }
        td { vertical-align: top; padding: 0; }
        .header td, .footer td { vertical-align: middle; }
    </style>
    </head>
    <body>
    <div class="card">

        <div class="header">
            <table width="100%">
                <tr>
                    <td class="brand">GYM</td>
                    <td style="text-align: right;" class="label-top">ACCESS CARD</td>
                </tr>
            </table>
        </div>
        <div class="accent"></div>

        <div class="content">
            <table width="100%">
                <tr>
                    <td style="width: 58pt;">{$photo_html}</td>
                    <td>
                        <div class="name">{$full_name}</div>
                        <table>
                            <tr>
                                <td class="row-label">Plan</td>
                                <td class="row-value">{$plan}</td>
                            </tr>
                            <tr>
                                <td class="row-label">Trainer</td>
                                <td class="row-value">{$trainer}</td>
                            </tr>
                            <tr>
                                <td class="row-label">Since</td>
                                <td class="row-value">{$member_since}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        <div class="footer">
            <table width="100%">
                <tr>
                    <td>Card No.</td>
                    <td style="text-align: right;" class="card-no">{$card_no}</td>
                </tr>
            </table>
        </div>

    </div>
    </body>
    </html>
    HTML;
    return $html;
}