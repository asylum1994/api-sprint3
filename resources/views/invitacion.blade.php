<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle y Código QR</title>
</head>
<body>
    
    <div>
        <h2>Detalle:</h2>
        <p>Aquí puedes agregar el detalle que deseas mostrar.</p>
    </div>


    <div>
        <h2>Código QR:</h2>
        <h3>escanear para descargar la aplicacion PASANAKU</h3>
        <img src="{{ $message->embed(public_path() . '/pasanaku_qrcode.png') }}" alt="" width="400" style="height:auto;display:block; padding-bottom: 20px;" />
    </div>
    
</body>



</html>
