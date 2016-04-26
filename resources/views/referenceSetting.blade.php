<div class="row reference_setting_interval">
    <hr/>
    <div class="col-md-2">
        <input type="time" class="form-control" name="interval_start" value="{{ $reference['start'] or '00:00' }}" placeholder="Start" data-array="1">
    </div>
    <div class="col-md-2">
        <input type="time" class="form-control" name="interval_end" value="{{ $reference['end'] or '00:00' }}" placeholder="End" data-array="1">
    </div>
    <div class="col-md-2">
        <div class="input-group">
            <input type="text" class="form-control" name="interval_attr" value="{{ $reference['attribute'] or '' }}" placeholder="Attribute" readonly data-array="1">
            <div class="input-group-btn">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button>
                <ul class="dropdown-menu">
                    <li><a onclick="$(this).parents('.input-group').children('input').val('MB');">最忙时</a></li>
                    <li><a onclick="$(this).parents('.input-group').children('input').val('B');">忙时</a></li>
                    <li><a onclick="$(this).parents('.input-group').children('input').val('F');">闲时</a></li>
                    <li><a onclick="$(this).parents('.input-group').children('input').val('N');">非营业</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="input-group">
            <input type="number" class="form-control inputValue" name="interval_value" value="{{ $reference['value'] or '' }}" placeholder="Reference Value" data-array="1">
            <input type="hidden" name="interval_type" value="manual" data-array="1">
            <div class="input-group-btn">
                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span><span class="sr-only">Toggle Dropdown</span></button>
                <ul class="dropdown-menu">
                    <li><a onclick="switchReferenceType(this,'date')">Date</a></li>
                    <li><a onclick="switchReferenceType(this,'manual')">Manual</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-2">
        <input class="form-control" name="interval_powercost" type="number" data-array="1" placeholder="￥/KWH" value="{{ $reference['powercost'] or '' }}">
    </div>
    <div class="col-md-1">
        <div class="btn-group btn-group-xs">
            <button class="btn btn-sm btn-default" onclick="addReferenceSettingLine(this)">
                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span>
            </button>
            <button class="btn btn-sm btn-default" onclick="deleteReferenceSettingLine(this)">
                <span class="glyphicon glyphicon-minus" aria-hidden="true"></span>
            </button>
        </div>
    </div>
</div>