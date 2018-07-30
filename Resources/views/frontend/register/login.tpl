{extends file='parent:frontend/register/login.tpl'}

{block name="frontend_register_login_input_lostpassword" append}
    {* Persistent login *}
    {block name='frontend_register_login_input_form_persistent_login'}
        <div class="register--login-persistent">
            <input name="persistent" type="checkbox" id="persistent" value="1" checked="checked" />
            <label for="persistent">{s name="soft_login_label"}{/s}</label>
        </div>
    {/block}
{/block}
