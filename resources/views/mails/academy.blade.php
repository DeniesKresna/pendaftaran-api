<h3>Halo, {{ $name }} !</h3>
 
<p>Kami mau menginformasikan pembayaran kamu untuk Jobhun Akademi yang ada di bawah ini:</p>
@foreach($ja_list as $ja)
	<ul>
		<li>{{$ja['name']}} - {{$ja['period']}}</li>
	</ul>
@endforeach
<p><b>{{ $status }}</b></p>
<p>Jika ada keluhan / pertanyaan silakan menghubungi kami di :</p>
<p>Whatsapp: 08113235533</p>
<p>Email: info@jobhun.id</p>