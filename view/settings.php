<div class="<?php echo $this->text_domain ?> settings"> 
    <h1>Settings </h1>
    <form>
        <div class=" form-area"> 
            <div class="form-group">
                <label> Tooltip background color </label>
                <input type="text" data-default-color="#444" class="color-field form-control " name="bg_color" value="" />
            </div>


            <div class="form-group">
                <label> Tooltip text color  </label>
                <input type="text" data-default-color="#444" class="color-field form-control " name="text_color" value="" />
            </div>


            <div class="form-group">
                <label> Tooltip popup position   </label>
                <select name="popup_position" class="form-control ">
                    <option value="top">Top</option>
                    <option value="bottom">Bottom</option>
                    <option value="left">Left</option>
                    <option value="right">Right</option>
                </select>
            </div>

            <div class="form-group">
                <label> Tooltip popup width  </label>
                <input type="text" name="popup_width" class="form-control " />
            </div>
            <br/>

            <div  class="form-group text-center" >
                <input type="submit" name="submit" class="button button-primary button-large" value="Save" />

            </div>


        </div>

        <div class="clearfix"></div>


    </form>

</div>