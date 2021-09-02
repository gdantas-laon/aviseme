<?php
class Controllerextensionmoduleaviseme extends Controller{
    public function index()
    {

        return $this->load->view('extension/module/aviseme');
    }

    public function getForm()
    {
        $return = array();
        $this->load->language('product/product');
        if($this->customer->isLogged()){
            $product_id = $this->request->get['product_id'];
            $return['success'] = $this->language->get('text_loggedCustomer');
            $this->validaFormLogged($product_id);
            $this->response->setOutput(json_encode($return, JSON_UNESCAPED_UNICODE));
            return true;
        }
        $return['success'] = true;
        $this->response->setOutput(json_encode($return));
        return false;

    }

    public function validaFormLogged($product_id)
    {
       $email = $this->customer->getFirstName();
        $name = $this->customer->getEmail();
        $this->createProductRequest($email,$product_id);
    }

    public function validateForm(): void
    {
        $response = array();
        $this->load->language('product/product');
        $email = $this->request->post['email'];
        $product_id = $this->request->post['product_id'];
        $email = filter_var($email,FILTER_SANITIZE_EMAIL);
        if(!isset($product_id)){
            $response['error'] = $this->language->get('text_undefined_error');
            $this->response->setOutPut(json_encode($response,JSON_UNESCAPED_UNICODE));
        }
        if(!empty($email) || $email !== "" ){
            //verifica se e-mail esta no formato correto de escrita
            if (!filter_var($email,FILTER_VALIDATE_EMAIL)){
                $response['error'] = $this->language->get('text_invalid_email');
                $this->response->setOutPut(json_encode($response,JSON_UNESCAPED_UNICODE));

            }
            else{
                //Valida o dominio
                $dominio=explode('@',$email);
                if(!filter_var($dominio[1],FILTER_VALIDATE_DOMAIN,FILTER_FLAG_HOSTNAME)){
                    $response['error'] = $this->language->get('text_invalid_email');
                    $this->response->setOutPut(json_encode($response,JSON_UNESCAPED_UNICODE));

                }
                else{
                    $this->createProductRequest($email,$product_id);
                } // Retorno true para indicar que o e-mail é valido
            }
        }else {
            $response['error'] = $this->language->get('text_blank_email');
            $this->response->setOutPut(json_encode($response),JSON_UNESCAPED_UNICODE);

        }
    }

    public function createProductRequest($email, $product_id)
    {
        $response = array();
        $filter_data = array();
        $filter_data = [
            'customer_email' => $email,
            'product_id' => $product_id
        ];
        try {
            $this->load->model('extension/module/aviseme');
            $this->model_extension_module_aviseme->install();
            $this->model_extension_module_aviseme->createRequest($filter_data);
            $response['success'] = $this->language->get('text_success_email');
            $this->response->setOutPut(json_encode($response), JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            $response['error'] =  $this->language->get('text_undefined_error') ;
            $this->response->setOutPut(json_encode($response), JSON_UNESCAPED_UNICODE);
        }

    }
}