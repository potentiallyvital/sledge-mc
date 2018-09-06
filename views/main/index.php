<div class="row">
        <div class="col-12 col-sm-12 col-md-6">
                <h1>H1 - Start Styling</h1>
                <h2>H2 - Sub Heading</h2>
        </div>
        <div class="col-12 col-sm-12 col-md-6">
                <h3>H3 - Sub Sub</h3>
                <h4>H4 - Subbitty Sub Sub</h4>
                <h5>H5 - Some Subs in the Tub</h5>
        </div>
</div>
<hr />
<div class="row">
        <div class="col-12 col-sm-12 col-md-6">
                Here, have a paragraph:
                <p>
                        Lorem ipsum dolor sit amet, eam in animal admodum
                        definitiones. Sint exerci eripuit quo at, melius
                        omnium voluptatum te per. Decore contentiones nam
                        no. Epicurei senserit urbanitas his ei, perfecto
                        interpretaris pro ne. At erant dicant omittantur
                        vim, cu tacimates scripserit mel, duo maiorum
                        invenire ad.
                </p>
        </div>
        <div class="col-12 col-sm-12 col-md-6">
                Everybody loves tables:
                <table>
                        <thead>
                                <tr>
                                        <th>thead</th>
                                        <th>th</th>
                                        <td>td</td>
                                </tr>
                        </thead>
                        <tbody>
                                <tr>
                                        <td>tbody</td>
                                        <td>tr</td>
                                        <td>And some long text, shall we?</td>
                                </tr>
                                <tr>
                                        <td>tbody</td>
                                        <td>tr</td>
                                        <td>This is not the row you're looking for</td>
                                </tr>
                        </tbody>
                        <tfoot>
                                <tr>
                                        <th>tfoot</th>
                                        <th>th</th>
                                        <td>td</td>
                                </tr>
                        </tfoot>
                </table>
        </div>
</div>
<hr />
<div class="panel">
        <div class="panel-heading">
                Panel Heading
        </div>
        <div class="panel-body">
                Panel Body starts here
                <hr />
                <form>
                        <?=textbox('text');?>
                        <?=textarea('textarea');?>
                        <?=dropdown('dropdown', ['label'=>'Dropdown', 'options'=>['Zero','One','Two']]);?>
                        <?=checkboxes('checkbox', ['label'=>'Checkboxes', 'options'=>['Three','Four','Five']]);?>
                        <?=radios('radio', ['label'=>'Radios', 'options'=>['Six','Seven','Eight']]);?>
                        <?=button('button');?>
                </form>
        </div>
</div>
