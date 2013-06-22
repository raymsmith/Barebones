<html>
	<head>
		<title>MC</title>
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
	</head>
	<body>
		<input id="btn" type="button" value="Push Me"  />
		<textarea id="response"></textarea>
		<script>
			$(document).ready(function() {
			  //Simple Sample jQuery Ajax call
				$('#btn').click(function(){
					$.ajax({
						url: "http://localhost/html/mc/V1/general/common/",
						type: "PUT",
						data:{data:'random'},
						success: function(response){
							$('#response').val(JSON.stringify(response));
							// Create a table
						}
					});
				});
			});
		</script>
	</body>
</html>
