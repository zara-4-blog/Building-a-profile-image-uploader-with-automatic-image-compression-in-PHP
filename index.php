<?php

function prettyBytes($bytes, $decimalPlaces = 0, $space = false) {
  $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
  $divisions = 0;
  while($bytes >= 1024) {
    $bytes /= 1024;
    $divisions++;
  }
  return number_format($bytes, $decimalPlaces) . ($space ? ' ' : '') . $units[$divisions];
}

?>
<!DOCTYPE html>
<html>
<head>

  <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">

  <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet"
    integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN"
    crossorigin="anonymous">

  <script
    src="https://code.jquery.com/jquery-1.12.4.min.js"
    integrity="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="
    crossorigin="anonymous"></script>

</head>
<body>

  <div class="container">
    <div class="row">
      <div class="col-xs-12 col-sm-10 col-md-8 col-lg-6 col-sm-offset-1 col-md-offset-2 col-lg-offset-3">

        <h1>Edit your profile</h1>

        <hr/>

        <?php
        $metaData = file_exists('metadata.json') ? json_decode(file_get_contents('metadata.json')) : [];
        $id = isset($metaData->{'id'}) ? $metaData->{'id'} : null;

        $hasImage = $id && file_exists('uploaded-profile-'.$id);
        $profileUrl = $hasImage ? 'uploaded-profile-'.$id : 'img/default-profile.png';
        ?>
        <div class="media">
          <div class="media-left" style="padding-right: 30px">
            <img style="width: 140px; height: 140px;" class="media-object img-circle img-thumbnail" src="<?php echo $profileUrl ?>" alt="User Profile Image">
          </div>
          <div class="media-body">
            <h3 class="media-heading">Upload a new profile image</h3>
            <form action="update-profile-image.php" method="post" enctype="multipart/form-data" id="form">

              <div style="margin-top:15px" class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><b>Select Image</b></span>
                  <input class="form-control" type="file" name="file-to-upload" id="file-to-upload">
                </div>
              </div>

              <div class="form-group">
                <div class="input-group">
                  <span class="input-group-addon"><b>Resize Mode</b></span>
                  <select name="resize-mode" class="form-control">
                    <option value="stretch">Stretch</option>
                    <option value="crop">Crop</option>
                  </select>
                </div>
              </div>

              <div style="margin-top: 15px">
                <button class="btn btn-primary" id="submit-btn">
                  <i class="fa fa-cloud-upload"></i>&nbsp;&nbsp;Upload
                </button>
                <?php if ($hasImage): ?>
                <button class="btn btn-danger" name="reset" id="reset">
                  <i class="fa fa-close"></i>&nbsp;&nbsp;Reset
                </button>
                <?php endif; ?>
              </div>

            </form>

            <?php if ($hasImage): ?>
            <table class="table"style="margin-top: 60px">
              <tr>
                <th>Percentage Saving</th>
                <td><?php echo number_format($metaData->{'percentage-saving'}, 2) ?>%</td>
              </tr>
              <tr>
                <th>Original File Size</th>
                <td><?php echo prettyBytes($metaData->{'original-file-size'}, 1, true); ?></td>
              </tr>
              <tr>
                <th>Compressed File Size</th>
                <td><?php echo prettyBytes($metaData->{'compressed-file-size'}, 1, true); ?></td>
              </tr>
            </table>
            <?php endif; ?>
          </div>
        </div>

      </div>
    </div>
  </div>

  <script>
    $(function() {
      $('#form').on('submit', function() {
        $('#submit-btn').attr('disabled', 'disabled');
      });
    });
  </script>

</body>
</html>