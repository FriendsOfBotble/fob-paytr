<ol>
    <li>
        <p>
            <a href="https://www.paytr.com/" target="_blank">
                {{ trans('plugins/paytr::paytr.instructions.step_1', ['name' => 'PayTR']) }}
            </a>
        </p>
    </li>
    <li>
        <p>
            {{ trans('plugins/paytr::paytr.instructions.step_2', ['name' => 'PayTR']) }}
        </p>
    </li>
    <li>
        <p>
            {{ trans('plugins/paytr::paytr.instructions.step_3') }}
        </p>
    </li>
    <li>
        <p>
            {!!
                BaseHelper::clean(trans('plugins/paytr::paytr.instructions.step_4'))
            !!}
        </p>

        <code>{{ route('payments.paytr.webhook') }}</code>
    </li>
</ol>
