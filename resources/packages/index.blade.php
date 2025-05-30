<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <title>Document</title>
</head>
<body>
    <div class="row mt-5">
        <div class="col-md-6 offset-md-3">
            <div class="col-md-3 mt-3 mb-5">
                <button onclick="appendNew()" class="btn btn-secondary">Add New Model</button>
            </div>
            <form class="row g-3" method="POST" action="{{ route('add.models') }}">
                @csrf
                <div id="container">
                    <div class="form-group mb-5">
                        <div class="row">
                            <div class="col-md-6">
                                <input name="models[0][name]" type="text" class="form-control" placeholder="Model Name">
                            </div>
                            <div class="col-md-3">
                                <input name="models[0][migration]" type="checkbox" class="btn-check" id="migration0" autocomplete="off">
                                <label class="btn btn-outline-primary" for="migration0">Create Migration</label>
                            </div>
                            <div class="col-md-3">
                                <input name="models[0][factory]" type="checkbox" class="btn-check" id="factory0" autocomplete="off">
                                <label class="btn btn-outline-primary" for="factory0">Create Factory</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-auto mt-5">
                    <button type="submit" class="btn btn-primary mb-3">Create</button>
                </div>
            </form>
        </div>
    </div>


    <script>
        var index = 1;

        function appendNew() {
            let html = `
                <div class="form-group mb-5">
                    <div class="row">
                        <div class="col-md-6">
                            <input name="models[${index}][name]" type="text" class="form-control" placeholder="Model Name">
                        </div>
                        <div class="col-md-3">
                            <input name="models[${index}][migration]" type="checkbox" class="btn-check" id="migration${index}" autocomplete="off">
                            <label class="btn btn-outline-primary" for="migration${index}">Create Migration</label>
                        </div>
                        <div class="col-md-3">
                            <input name="models[${index}][factory]" type="checkbox" class="btn-check" id="factory${index}" autocomplete="off">
                            <label class="btn btn-outline-primary" for="factory${index}">Create Factory</label>
                        </div>
                    </div>
                </div>
            `;
            $('#container').append(html);
            index++;
        }
    </script>
</body>
</html>