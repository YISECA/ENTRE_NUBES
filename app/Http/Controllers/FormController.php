<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB as DB;
use Redirect;
use Validator;
use Session;
use App\Form;
use Idrd\Usuarios\Repo\Acceso;
use Mail;



class FormController extends BaseController

{
    var $url;
    private function cifrar($M)

    {   

      $C="";
      $k = 18; 
      for($i=0; $i<strlen($M); $i++)$C.=chr((ord($M[$i])+$k)%255);
      return $C;
    }

    private function decifrar($C)
    {   

      $M="";
      $k = 18;
      for($i=0; $i<strlen($C); $i++)$M.=chr((ord($C[$i])-$k+255)%255);
      return $M;
    }



    public function listar_datos()
    {

      $acceso = Form::where('fecha_caminata','2017-09-10')->whereYear('created_at', '=', date('Y'))->get();
      //$acceso = Form::whereYear('created_at', '=', date('Y'))->get(); 

      $tabla='<table id="lista">

        <thead>
           <tr>
             <th style="text-transform: capitalize;">id</th>
             <th style="text-transform: capitalize;">cedula</th>
             <th style="text-transform: capitalize;">tipo_documento</th>
             <th style="text-transform: capitalize;">primer_nombre</th>
             <th style="text-transform: capitalize;">segundo_nombre</th>
             <th style="text-transform: capitalize;">primer_apellido</th>
             <th style="text-transform: capitalize;">segundo_apellido</th>
             <th style="text-transform: capitalize;">genero</th>
             <th style="text-transform: capitalize;">fecha_nacimiento</th>
             <th style="text-transform: capitalize;">mail</th>
             <th style="text-transform: capitalize;">celular</th>
             <th style="text-transform: capitalize;">eps</th>          
          </tr>
        </thead>
        <tbody id="tabla">';

      foreach ($acceso as $key => $value)
      {

       $tabla.='<tr><td>'.$value->id.'</td>';
       $tabla.='<td>'.$value->cedula.'</td>';
       $tabla.='<td>'.$value->tipo_documento.'</td>';
       $tabla.='<td>'.$value->primer_nombre.'</td>';
       $tabla.='<td>'.$value->segundo_nombre.'</td>';
       $tabla.='<td>'.$value->primer_apellido.'</td>';
       $tabla.='<td>'.$value->segundo_apellido.'</td>';
       $tabla.='<td>'.$value->genero.'</td>';
       $tabla.='<td>'.$value->fecha_nacimiento.'</td>';
       $tabla.='<td>'.$value->mail.'</td>';
       $tabla.='<td>'.$value->celular.'</td>';
       $tabla.='<td>'.$value->eps.'</td></tr>';
      }

      $tabla.='</tbody></table>';
      echo $tabla;
    }

  
    public function logear(Request $request)
    {

      $usuario = $request->input('usuario');
      $pass = $request->input('pass');
      $acceso = Acceso::where('Usuario',$usuario)->where('Contrasena', sha1($this->cifrar($pass)) )->first();

      if (empty($usuario)) { return view('error',['error' => 'Usuario o contraseña invalida!'] ); exit(); }
      if (empty($acceso)) { return view('error',['error' => 'Usuario o contraseña invalida!'] ); exit(); }
       
       session_start() ;
       $_SESSION['id_usuario'] = json_encode($acceso);

      return view('admin'); exit(); 
    }


    public function insertar(Request $request)
    {

      $post = $request->input();
      $usuario = Form::where('cedula', $request->input('cedula'))->first(); 
      if (!empty($usuario)) { return view('error',['error' => 'Este usuario ya fue registrado!'] ); exit(); }
      $formulario = new Form([]);

        //envio de correo

       if($this->inscritos()<=40)
       {

        $this->store($formulario, $request->input());
        Mail::send('email', ['user' => $request->input('mail')], function ($m) use ($request) 
        {

            $m->from('no-reply@idrd.gov.co', 'Registro Exitoso a esta Caminata');
            $m->to($request->input('mail'), $request->input('primer_nombre'))->subject('Registro Exitoso a esta caminata!');

        });

      }else{

        return view('error', ['error' => 'Lo sentimos el limite de inscritos fue superado!']);
      }

        return view('error', ['error' => 'Registro insertado!']);
    }


// conteo de la tabla

    private function inscritos()
    {

      $cant = Form::count('id');
      return $cant+1;
    }


    private function store($formulario, $input)

    {

        $formulario['cedula'] = $input['cedula'];
        $formulario['tipo_documento'] = $input['tipo_documento'];
        $formulario['primer_nombre'] = $input['primer_nombre'];
        $formulario['segundo_nombre'] = $input['segundo_nombre'];
        $formulario['primer_apellido'] = $input['primer_apellido'];
        $formulario['segundo_apellido'] = $input['segundo_apellido'];
        $formulario['genero'] = $input['genero'];
        $formulario['fecha_nacimiento'] = $input['fecha_nacimiento'];
        $formulario['mail'] = $input['mail'];
        $formulario['celular'] = $input['celular'];
        $formulario['eps'] = $input['eps'];
        $formulario['fecha_caminata'] = $input['fecha_caminata'];
        $formulario->save();
        return $formulario;
    }

}

