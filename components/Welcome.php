<?php


namespace PHP7API\Components;


class Welcome extends Component{

    public function welcome($request, $route){

        return [
            'success' => 1,
            'message' => 'success'
        ];
    }
}
