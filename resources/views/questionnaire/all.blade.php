@extends('loggedin-environment.layout')

@section('content-header')
    <h1>Manage Questionnaires</h1>
@stop

@push('css')
    <link rel="stylesheet" type="text/css" href="{{asset('dist/css/manage-questionnaires.css')}}">
@endpush

@section('content')
    <div class="row manage-questionnaires">
        <div class="col-md-12 col-xs-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">All questionnaires</h3>
                </div>
                <div class="card-body">
                    <div class="row margin-bottom">
                        <div class="col-md-2">
                            <a class="btn btn-block btn-primary new-questionnaire"
                               href="{{route("create-questionnaire")}}"><i
                                        class="fa fa-plus"></i> Create new questionnaire</a>
                        </div>
                    </div>
                    <table class="w-100 table table-striped" id="questionnaires-table" cellspacing="0">
                        <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th>Title</th>
                            <th>Project</th>
                            <th>Goal / Responses</th>
                            <th>Languages available</th>
                            <th>Status</th>
                            <th class="text-center">Order</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($viewModel->questionnaires as $questionnaire)
                            <tr data-id="{{$questionnaire->id}}" data-title="{{$questionnaire->title}}"
                                data-status="{{$questionnaire->status_id}}">
                                <td class="text-center">{{ $loop->index + 1 }}</td>
                                <td>{{$questionnaire->title}}</td>
                                <td>{{ $questionnaire->project_name }}</td>
                                <td>{{ $questionnaire->goal }} / {{ $questionnaire->number_of_responses }}
                                    <b>({{ round(($questionnaire->number_of_responses / $questionnaire->goal) * 100, 1) }}
                                        %)</b></td>
                                <td>
                                    <b>{{$questionnaire->default_language_name}}</b>
                                    {{$questionnaire->languages}}
                                </td>
                                <td>
                                        <span class="badge {{$viewModel->setCssClassForStatus($questionnaire->status_id)}}"
                                              title="{{$questionnaire->status_description}}">{{$questionnaire->status_title}}</span>
                                </td>
                                <td class="text-center">{{ $questionnaire->prerequisite_order }}</td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-primary dropdown-toggle" type="button"
                                                data-toggle="dropdown">Select an action
                                            <span class="caret"></span></button>
                                        <div class="dropdown-menu">
                                            <a class="action-btn dropdown-item"
                                               href="{{route('edit-questionnaire', ['id' => $questionnaire->id])}}"><i
                                                        class="far fa-edit"></i> Edit Questionnaire</a>
                                            <a class="action-btn dropdown-item"
                                               href="{{route('statistics-colors', ['questionnaire' => $questionnaire->id])}}"><i
                                                        class="fas fa-palette"></i> Statistics Colors</a>
                                            <a class="action-btn dropdown-item"
                                               href="{{route('translate-questionnaire', ['id' => $questionnaire->id])}}"><i
                                                        class="fa fa-language"></i> Translate</a>
                                            @if(isset($questionnaire->url) && $questionnaire->url)
                                                <button data-clipboard-text="{{ $questionnaire->url }}"
                                                   class="copy-clipboard action-btn dropdown-item">
                                                    <i class="copy-questionnaire-link fa fa-link"></i> Get Link
                                                </button>
                                            @endif
                                            <hr>
                                            <a class="action-btn dropdown-item"
                                               href="{{route('questionnaires.reports', ['questionnaireId' => $questionnaire->id])}}"><i
                                                        class="fas fa-list-ul"></i> View Results Report</a>
                                            <a class="action-btn dropdown-item"
                                               target="_blank"
                                               href="{{route('questionnaire.statistics', ['questionnaire' => $questionnaire->id])}}">
                                                <i class="fas fa-chart-pie"></i> View Statistics</a>
                                            @can('change-status-crowd-sourcing-projects')
                                                <hr>
                                                <a class="action-btn dropdown-item change-status"
                                                   href="javascript:void(0)"
                                                   data-toggle="modal"
                                                   data-target="#changeStatusModal"><i class="fa fa-cog"></i> Change
                                                    status</a>
                                            @endcan
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@push('modals')
    <div class="modal fade" id="changeStatusModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Change status for Questionnaire</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                </div>
                <form action="{{route('update-questionnaire-status')}}" method="post">
                    <input type="hidden" name="_token" value="{{csrf_token()}}">
                    <div class="modal-body">
                        <input type="hidden" name="questionnaire_id" id="questionnaire-id">
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <select name="status_id" id="status-select" class="form-control">
                                    @foreach($viewModel->statuses as $status)
                                        <option value="{{$status->id}}">{{$status->title}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row hide">
                            <div class="col-md-12 form-group">
                                <textarea name="comments" id="comments" class="form-control" cols="30"
                                          rows="5"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush

@push('scripts')
    <script src="{{asset('/dist/js/manageQuestionnaires.js')}}"></script>
@endpush
