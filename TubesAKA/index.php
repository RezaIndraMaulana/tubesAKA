<?php
include "connect.php";

if (!$connect) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

$resultsIterative = [];
$resultsRecursive = [];
$searchScore = '';
$timeIterative = [];
$timeRecursive = [];

function searchIterative($data, $score)
{
    global $timeIterative;
    $found = [];
    $startTime = microtime(true);

    for ($i = 0; $i < count($data); $i++) {
        $timeIterative[] = microtime(true) - $startTime;
        if ((float) $data[$i]['Score'] == (float) $score) {
            $found[] = $data[$i];
        }
    }

    return $found;
}

function searchRecursive($data, $score, $index = 0, $found = [], $startTime = null)
{
    global $timeRecursive;

    if ($startTime === null) {
        $startTime = microtime(true);
    }

    $timeRecursive[] = microtime(true) - $startTime;

    if ($index >= count($data)) {
        return $found;
    }

    if ((float) $data[$index]['Score'] == (float) $score) {
        $found[] = $data[$index];
    }

    return searchRecursive($data, $score, $index + 1, $found, $startTime);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $searchScore = $_POST['score'];

    $query = "SELECT * FROM anime";
    $result = mysqli_query($connect, $query);

    if ($result) {
        $animeData = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $animeData[] = $row;
        }

        $resultsIterative = searchIterative($animeData, $searchScore);
        $resultsRecursive = searchRecursive($animeData, $searchScore);
    }
}

mysqli_close($connect);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pencarian Filtering Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
    </style>
</head>

<body>
    <div class="container">
        <h1>Data Anime - Filtering Search</h1>
        <form method="POST" action="">
            <div class="mb-3">
                <input type="number" step="any" name="score" class="form-control" placeholder="Cari berdasarkan Score"
                    value="<?= htmlspecialchars($searchScore) ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>

        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
            <h3>Hasil Pencarian (Iteratif):</h3>
            <?php if (!empty($resultsIterative)): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>MAL_ID</th>
                            <th>Nama</th>
                            <th>Score</th>
                            <th>Genres</th>
                            <th>Episodes</th>
                            <th>Members</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultsIterative as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['MAL_ID']) ?></td>
                                <td><?= htmlspecialchars($row['Name']) ?></td>
                                <td><?= htmlspecialchars($row['Score']) ?></td>
                                <td><?= htmlspecialchars($row['Genres']) ?></td>
                                <td><?= htmlspecialchars($row['Episodes']) ?></td>
                                <td><?= htmlspecialchars($row['Members']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Tidak ada data yang ditemukan.</p>
            <?php endif; ?>

            <h3>Hasil Pencarian (Rekursif):</h3>
            <?php if (!empty($resultsRecursive)): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>MAL_ID</th>
                            <th>Nama</th>
                            <th>Score</th>
                            <th>Genres</th>
                            <th>Episodes</th>
                            <th>Members</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($resultsRecursive as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['MAL_ID']) ?></td>
                                <td><?= htmlspecialchars($row['Name']) ?></td>
                                <td><?= htmlspecialchars($row['Score']) ?></td>
                                <td><?= htmlspecialchars($row['Genres']) ?></td>
                                <td><?= htmlspecialchars($row['Episodes']) ?></td>
                                <td><?= htmlspecialchars($row['Members']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>Tidak ada data yang ditemukan.</p>
            <?php endif; ?>

            <div class="chart-container">
                <canvas id="timeChart"></canvas>
            </div>

            <script>
                var timeIterative = <?= json_encode($timeIterative) ?>;
                var timeRecursive = <?= json_encode($timeRecursive) ?>;

                // Gunakan indeks dari waktu yang tercatat sebagai label
                var labels = Array.from({ length: Math.max(timeIterative.length, timeRecursive.length) }, (_, i) => i + 1);

                var ctx = document.getElementById('timeChart').getContext('2d');
                var timeChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Iterative Search',
                                data: timeIterative,
                                borderColor: 'blue',
                                backgroundColor: 'rgba(0, 0, 255, 0.1)',
                                fill: false,
                                tension: 0.3
                            },
                            {
                                label: 'Recursive Search',
                                data: timeRecursive,
                                borderColor: 'red',
                                backgroundColor: 'rgba(255, 0, 0, 0.1)',
                                fill: false,
                                tension: 0.3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        },
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Search Attempt'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Time (seconds)'
                                },
                                beginAtZero: true
                            }
                        }
                    }
                });
            </script>

        <?php endif; ?>
    </div>
</body>

</html>