<?php
cabecera($sql, $cusers, $gene, 4);
?>
<div style="margin: 20px;">
<h1>El Curriculum Vitae</h1>
<p>Los profesionales dedicados a la contratación de personal esperan que el Curriculum Vitae sea la expresión clara y concisa de informaciones sobre los datos personales, la formación y la experiencia profesional de la persona que aspira a un empleo.</p>
<h3>Recuerda...</h3>
<p>El primer objetivo que buscas a la hora de preparar tu Curriculum Vitae es obtener un entrevista.<p>
<p><b>El Curriculum Vitae cumple una triple función:</b></p>
<ul style="margin-left: 20px;">
	<li>Presentarte a tu futuro empleador.</li>
	<li>Concentrar la atención durante la primera entrevista sobre los aspectos más importantes de tu personalidad y de tu recorrido académico y laboral.</li>
	<li>Después de la entrevista, recordar a tu futuro empleador los datos que mejor hablan de ti.</li>
	<li>De los puntos fuertes de tu biografía, tu Curriculum Vitae debe resaltar los que están en perfecta adecuación con la función que debes desempeñar en la empresa, pero sin mentir. Esto significa que a lo mejor debes modificar tu Curriculum dependiendo del puesto de trabajo al que te presentes.</li>
</ul>
<br /><br />
<h3>Cómo estructurar tu Curriculum Vitae</h3>
<p>Primero es preciso darle un título: "Curriculum Vitae" de (nombre y apellidos de la persona), o solamente "Curriculum Vitae".</p>
<p>A continuación, vienen las diferentes partes que un Curriculum Vitae siempre debe tener, distribuidas de la siguiente manera:</p>
<ul style="margin-left: 20px;">
	<li><b>Datos personales:</b><i> Nombre y apellidos, lugar y fecha de nacimiento, estado civil, dirección personal, número de teléfono de contacto, dirección de correo electrónico, etc.</i></li>
	<li><b>Formación académica:</b><i> Estudios que has realizado, indicando fechas, centro, y lugar donde han sido realizados.</i></li>
	<li><b>Otros Títulos y Seminarios:</b><i> Estudios realizados complementarios a los universitarios que mejoran tu formación universitaria, indicando las fechas, el Centro y el lugar donde fueron realizados.</i></li>
	<li><b>Experiencia Profesional:</b><i> Experiencia laboral relacionada con los estudios universitarios o que puedan ser de interés para la empresa que desea contratarte. No olvides señalar las fechas, la empresa dónde trabajaste y las funciones y tareas llevadas a cabo.</i></li>
	<li><b>Idiomas:</b><i> En este apartado mencionarás los idiomas que conoces y tu nivel. Si obtuviste algún título reconocido, como por ejemplo el 'First Certificate' en Inglés, que acredite tus conocimientos en estos ámbitos, indícalo.</i></li>
	<li><b>Informática:</b><i> Señala aquellos conocimientos informáticos que poseas:sistemas operativos, procesadores de texto, hojas de cálculo, bases de datos, diseño gráfico, internet, etc.</i></li>
	<li><b>Otros Datos de Interés:</b><i> En este último apartado señala todos aquellos aspectos que no han sido incluídos todavía, tales como:</b><i> Carné de conducir, disponibilidad, etc.</i></li>
</ul>
<br /><br />

<h3>Cómo presentar tu Curriculum Vitae</h3>
<blockquote>Existen tres maneras de presentar un Curriculum Vitae: la cronológica, la cronológica inversa, y la funcional.</blockquote>
<h4>El Curriculum Vitae cronológico</h4>
<p>Permite presentar la información partiendo de lo más antiguo a lo más reciente. Este formato tiene la ventaja de resaltar la evolución seguida. Pone de relieve, si cabe, la estabilidad y la evolución ascendente de tu carrera.</p>
<p>Su presentación cronológica ofrece el esquema ideal para la ulterior entrevista personal.</p>
<h4>El Curriculum Vitae cronológico inverso</h4>
<p>Menos tradicional, esta presentación gana cada día más terreno. Consiste en empezar por los datos más recientes. Tiene la ventaja de resaltar tus experiencias más recientes que son obviamente las que interesan más a las personas susceptibles de contratarte.</p>
<h4>El Curriculum Vitae funcional</h4>
<p>Distribuye la información por temas y proporciona un conocimiento rápido de tu formación y experiencia en un ámbito determinado. Es un perfecto instrumento de marketing porque, como no sigue una progresión cronológica, permite seleccionar los puntos positivos y omitir los eventuales errores de recorrido, los periodos de paro, los frecuentes cambios de trabajo...</p>
<p>El especialista en selección y contratación de personal está acostumbrado a estas tres formas de presentación de curriculum, por lo que deberas escoger la que mejor conviene a tu perfil profesional.</p>
<br /><br />


<h3>Modelos de curriculum</h3>
<ul style="margin-left: 20px;">
	<li><b>Cronológico: </b><?php showButton("cronologico", "success"); ?></li>
	<li><b>Funcional: </b><?php showButton("funcional", "danger"); ?></li>
	<li><b>Combinado: </b><?php showButton("combinado", "warning"); ?></li>
	<li><b>Europass: </b><a href="http://europass.cedefop.europa.eu/en/documents/curriculum-vitae" target="_blank" class="btn btn-default">Hazlo Online</a><a href="modelos/CV-europass.doc" target="_blank" class="btn btn-default">Descarga Plantilla</a></li>
</ul>
<br /><br />
<h3>Recuerda...</h3>
<ul style="margin-left: 20px;">
	<li>Tu curriculum no debe exceder de una o dos páginas.</li>
	<li>Tienes que cuidar el estilo y evitar los errores de ortografía.</li>
	<li>Antes de mandarlo, conviene someterlo a una lectura crítica por parte de terceros.</li>
	<li>Tienes que cuidar la imagen: papel de calidad, caracteres apropiados al contenido, presentación airosa que facilite la lectura...</li>
	<li>La fotografía adjunta tiene que ser reciente y de tamaño carné.</li>
</ul>
<br /><br />
<address>Fuente: GIPE</address>
<?php
pie();


function showButton($name, $style){
	for($x=1; $x<=4; $x++){
		?><a href="modelos/CV-<?php echo $name; ?>-modelo<?php echo $x; ?>.zip" target="_blank" class="btn btn-<?php echo $style; ?>">Modelo <?php echo $x; ?></a><?php
	}
}
?>