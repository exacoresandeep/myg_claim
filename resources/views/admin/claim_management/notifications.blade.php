<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="description" content="">
<meta name="author" content="">

<title>Notifications :: DMS</title>
@include("admin.include.header")
@include("admin.include.sidebar-menu")
  <div class="main-area">
    <h2 class="main-heading">Notifications</h2>    
    <div class="dash-all pt-0">
      <div class="dash-table-all">        
        <div class="sort-block">
          
        </div>
        <table class="table table-striped notification-table" id="notification-table">
          <thead>            
            <th>Sl.</th>
            <th>Date</th>
            <th>Message</th>
            <th>Action</th>
          </thead>
          <tbody>
            
          </tbody>
        </table>
        <div class="pagination-block">
          <ul class="pagination pagination-sm justify-content-end">
            <li class="page-item"><a class="page-link" href="#">Previous</a></li>
            <li class="page-item"><a class="page-link" href="#">Next</a></li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">
        @if(session()->has('message'))
        Swal.fire({
            title: "Success!",
            text: "{{ session()->get('message') }}",
            icon: "success",
        });
        @endif
        
        
        
        $(function () {
            var table = $('.notification-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ url('/notifications_list') }}",
                order: ['1', 'DESC'],
                pageLength: 10,
                columns: [
                    { 
                        data: 'TripClaimID', 
                        name: 'TripClaimID', 
                        render: function (data, type, row, meta) {
                            return meta.row + 1; // meta.row is zero-based index
                        }
                    },
                    { data: 'Date', name: 'Date' },
                    { data: 'Message', name: 'Message', orderable: false, searchable: false  },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });
        });

       

       
    </script>
@include("admin.include.footer")
