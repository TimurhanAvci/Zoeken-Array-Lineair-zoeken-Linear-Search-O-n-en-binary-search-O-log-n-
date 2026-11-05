<?php
// Lineair en binair zoeken in een gesorteerde array, inclusief tijdmeting

// In de browser (bijvoorbeeld via WAMP) tonen als platte tekst,
// zodat de regels en kolommen netjes onder elkaar blijven staan
if (php_sapi_name() !== 'cli') {
    header('Content-Type: text/plain; charset=utf-8');
}

// Lineair zoeken: loopt van het eerste tot het laatste element
// en stopt zodra het target gevonden is. Tijdcomplexiteit: O(n)
function linearSearch(array $array, int $target): bool
{
    foreach ($array as $element) {
        if ($element == $target) {
            return true;
        }
    }
    return false;
}

// Binair zoeken (recursief): kijkt naar het midden van het gedeelte dat nog
// over is en gooit telkens de helft weg. Werkt alleen op een gesorteerde array.
// Tijdcomplexiteit: O(log n)
function binarySearch(array $array, int $target, int $low, int $high): bool
{
    // Zoekruimte is op: het target zit er niet in
    if ($low > $high) {
        return false;
    }

    // Middelste index (afgerond naar beneden)
    $mid = intdiv($low + $high, 2);

    if ($array[$mid] == $target) {
        return true;
    } elseif ($array[$mid] > $target) {
        // Target is kleiner: zoek verder in de eerste helft
        return binarySearch($array, $target, $low, $mid - 1);
    } else {
        // Target is groter: zoek verder in de tweede helft
        return binarySearch($array, $target, $mid + 1, $high);
    }
}

// Maakt een array met $aantal willekeurige getallen en sorteert die van laag naar hoog
function maakGesorteerdeArray(int $aantal, int $min, int $max): array
{
    $getallen = [];
    for ($i = 0; $i < $aantal; $i++) {
        $getallen[] = mt_rand($min, $max);
    }
    sort($getallen);
    return $getallen;
}

// Meet hoe lang één zoekopdracht duurt, in microseconden
function meetTijd(callable $zoekFunctie): array
{
    $start = hrtime(true);
    $gevonden = $zoekFunctie();
    $eind = hrtime(true);
    return [$gevonden, ($eind - $start) / 1000];
}

// Voert alle tests uit op een gesorteerde array van $aantal getallen
function voerTestUit(int $aantal, int $herhalingen): void
{
    $max = $aantal * 10;
    $getallen = maakGesorteerdeArray($aantal, 1, $max);
    $laatsteIndex = count($getallen) - 1;

    echo "=== Test met $aantal getallen (gesorteerd van laag naar hoog) ===\n";
    echo "Kleinste: " . $getallen[0] . ", grootste: " . $getallen[$laatsteIndex] . "\n\n";

    // Vier situaties: eerste element, midden, laatste element en een getal dat er niet in zit
    $testen = [
        "Eerste element"  => $getallen[0],
        "Middelste element" => $getallen[intdiv($laatsteIndex, 2)],
        "Laatste element" => $getallen[$laatsteIndex],
        "Niet aanwezig"   => $max + 1,
    ];

    printf("%-20s %-10s %-18s %-18s\n", "Target", "Gevonden", "Lineair (µs)", "Binair (µs)");
    foreach ($testen as $naam => $target) {
        [$gevondenLineair, $tijdLineair] = meetTijd(fn() => linearSearch($getallen, $target));
        [$gevondenBinair, $tijdBinair] = meetTijd(fn() => binarySearch($getallen, $target, 0, $laatsteIndex));

        printf(
            "%-20s %-10s %-18s %-18s\n",
            $naam,
            $gevondenLineair === $gevondenBinair ? ($gevondenLineair ? "ja" : "nee") : "FOUT",
            number_format($tijdLineair, 1),
            number_format($tijdBinair, 1)
        );
    }

    // Gemiddelde over meerdere willekeurige zoekopdrachten, voor een eerlijker vergelijking
    $targets = [];
    for ($i = 0; $i < $herhalingen; $i++) {
        $targets[] = $getallen[mt_rand(0, $laatsteIndex)];
    }

    $start = hrtime(true);
    foreach ($targets as $target) {
        linearSearch($getallen, $target);
    }
    $totaalLineair = (hrtime(true) - $start) / 1000000;

    $start = hrtime(true);
    foreach ($targets as $target) {
        binarySearch($getallen, $target, 0, $laatsteIndex);
    }
    $totaalBinair = (hrtime(true) - $start) / 1000000;

    echo "\n$herhalingen willekeurige zoekopdrachten samen:\n";
    echo "Lineair zoeken: " . number_format($totaalLineair, 3) . " ms\n";
    echo "Binair zoeken:  " . number_format($totaalBinair, 3) . " ms\n";
    if ($totaalBinair > 0) {
        echo "Binair is ongeveer " . round($totaalLineair / $totaalBinair) . " keer sneller\n";
    }
    echo "\n";
}

// Hoofdprogramma
voerTestUit(10000, 1000);
voerTestUit(1000000, 20);
?>
