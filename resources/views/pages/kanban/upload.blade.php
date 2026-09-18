@extends('layouts.root.dashboard')


@section('main')
    <div class="container-fluid">


        <div class="section-header">

            <h1>
                Upload Kanban PDF
            </h1>

        </div>



        <div class="card">


            <div class="card-body">


                <div class="form-group">


                    <label>
                        Select Kanban PDF
                    </label>


                    <input type="file" id="files" multiple accept="application/pdf" class="form-control">


                    <small class="text-muted">

                        Only PDF file allowed.
                        Multiple file upload supported.

                    </small>


                </div>




                <button id="upload" class="btn btn-primary">

                    <i class="fas fa-upload"></i>

                    Process PDF

                </button>




                <div id="message" class="mt-3">

                </div>



                <div id="download" class="mt-3">

                </div>



            </div>


        </div>


    </div>





    <script>
        document
            .getElementById('upload')
            .addEventListener(
                'click',
                async function() {


                    let files =

                        document
                        .getElementById('files')
                        .files;



                    if (files.length === 0) {

                        alert(
                            'Please select PDF file'
                        );

                        return;

                    }



                    let formData =
                        new FormData();



                    for (
                        let i = 0; i < files.length; i++
                    ) {


                        formData.append(
                            'files[]',
                            files[i]
                        );


                    }





                    let message =
                        document
                        .getElementById('message');



                    message.innerHTML = `

        <div class="alert alert-info">

            Uploading and processing PDF...

        </div>

    `;



                    document
                        .getElementById('upload')
                        .disabled = true;





                    try {


                        let response =

                            await fetch(

                                "{{ route('dashboard.kanbanUploadProcess') }}",

                                {


                                    method: 'POST',



                                    headers: {


                                        'X-CSRF-TOKEN':

                                            document
                                            .querySelector(
                                                'meta[name="csrf-token"]'
                                            )
                                            .getAttribute(
                                                'content'
                                            )


                                    },


                                    body: formData


                                }

                            );




                        let result =

                            await response.json();





                        if (result.status) {


                            message.innerHTML = `


                <div class="alert alert-success">

                    ${result.message}

                </div>


            `;



                            /*
                                sementara reload

                                nanti diganti polling
                                status job

                            */


                            setTimeout(
                                function() {

                                    let jobId = result.job_id;



                                    let checker = setInterval(
                                        async function() {


                                                let response =
                                                    await fetch(
                                                        "/dashboard/kanban/status/" + jobId
                                                    );



                                                let status =
                                                    await response.json();



                                                if (status.status === "DONE") {


                                                    clearInterval(checker);



                                                    document
                                                        .getElementById('message')
                                                        .innerHTML = `

            <div class="alert alert-success">

                PDF Ready

            </div>

        `;



                                                    document
                                                        .getElementById('download')
                                                        .innerHTML = `

            <a href="${status.download}"

               class="btn btn-success">

                Download PDF

            </a>

        `;


                                                }


                                            },
                                            3000
                                    );

                                },
                                5000
                            );


                        } else {


                            message.innerHTML = `


                <div class="alert alert-danger">

                    Failed processing PDF

                </div>


            `;


                        }



                    } catch (error) {


                        console.error(error);



                        message.innerHTML = `


            <div class="alert alert-danger">

                System error

            </div>


        `;


                    }



                    document
                        .getElementById('upload')
                        .disabled = false;



                });
    </script>
@endsection
