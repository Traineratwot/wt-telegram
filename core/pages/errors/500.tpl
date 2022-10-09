{extends file='base.tpl'}
{block name="head"}

{/block}
{block name='content'}
    {if $msg}
        {$msg}
    {else}
		Server Error
    {/if}
{/block}
