@php
    use SimpleSoftwareIO\QrCode\Facades\QrCode;
@endphp
<!DOCTYPE html>
<html lang="fr">

    <head>
        <meta charset="UTF-8">
        <title>Ticket - {{ $event->titre }}</title>
        <style>
            body {
                border: 10px solid #FF5A00;
                /* Bordure orange autour de la page */
                padding: 20px;
                position: relative;
                min-height: 100vh;
                font-family: Arial, sans-serif;
            }

            .logo {
                position: absolute;
                bottom: 20px;
                right: 20px;
            }

            .ticket {
                margin-bottom: 30px;
                border: 2px dashed #FF5A00;
                padding: 20px;
                background-color: #fff8f0;
            }

            .qr-code {
                text-align: center;
                margin-top: 20px;
            }

            h1 {
                text-align: center;
                color: #FF5A00;
            }

            table {
                margin: 0 auto;
                margin-bottom: 20px;
                font-size: 16px;
            }

            table td {
                padding: 5px 10px;
            }

            .section-event {
                text-align: center;
                margin-bottom: 30px;
            }

            .section-event p {
                margin: 5px 0;
            }
        </style>
    </head>

    <body>

        <div class="section-event">
            <h1>{{ $event->titre }}</h1>
            <p><strong>Date :</strong> {{ \Carbon\Carbon::parse($event->date_debut)->format('d/m/Y') }}</p>
            <p><strong>Ville :</strong> {{ $event->ville }}</p>
        </div>

        @foreach ($tickets as $index => $ticket)
            @php
                $qrcode = base64_encode(QrCode::format('svg')->size(200)->generate($ticket->token));
            @endphp

            <div class="ticket">
                <h3 style="text-align: center;">Ticket N°{{ $index + 1 }}</h3>
                <table>
                    <tr>
                        <td><strong>Nom :</strong></td>
                        <td>{{ $ticket->client->user->username }}</td>
                    </tr>
                    <tr>
                        <td><strong>Numéro :</strong></td>
                        <td>{{ $ticket->client->user->telephone }}</td>
                    </tr>
                </table>
                <div class="qr-code">
                    <img src="data:image/png;base64,{{ $qrcode }}" alt="QR Code">
                </div>
            </div>
        @endforeach

        <!-- Logo ou image en bas à droite -->
        <img class="logo" src="{{ asset('assets/img/orange-money-logo.jpg') }}" width="100" alt="Logo Orange Money">

    </body>

</html>
