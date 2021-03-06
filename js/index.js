function sendTask(){

	var text = $('#input-task').val();
	var status = "undone";
	var token = localStorage.getItem("token");

	$.ajax({
		url:"../api.php?action=addtask",
		method:"POST",
		dataType: 'json',
		cache:false,
		data:{
			text:text,
			status:status,
			token:token
		},
		success:function(){
			getTasks();
		},
		error:function(){ 
		}
	});
};

function getTasks(){
	var token = localStorage.getItem("token");

	$.ajax({ 
		url: "../api.php?action=gettasks",
		method:"GET",
		dataType: 'json',
		cache:false,
		data:{
			token:token
		},
        success: function(data){
        	fillTasksList(data);	
        }
    });
};

function deleteTask(id_task){
	var token = localStorage.getItem("token");
	$.ajax({ 
		url: "../api.php?action=deletetask",
		method:"POST",
		dataType:'json',
		cache:false,
		data:{
			token:token,
			id:id_task
		},
        success: function(){
        	$('#tasks').empty();
        	getTasks();	
        }
    });
};

function changeStatus(id_task, status){
	var token = localStorage.getItem("token");

	$.ajax({ 
		url: "../api.php?action=changestatus",
		method:"POST",
		dataType:'json',
		cache:false,
		data:{
			token:token,
			id:id_task,
			status:status
		},
        success: function(){
        	$('#tasks').empty();
        	getTasks();	
        }
    });
};

$(document).ready(function(){
	$('#logout').click(function(){
		$.ajax({
			url:"../api.php?action=logout",
			method:"GET",
			dataType: 'json',
			cache:false,
			success:function(){
				localStorage.removeItem('token');
				window.location="auth.php";
			},
			error:function(){ 
			}
		});
	});
});

$(document).ready(function(){
	getTasks();
});

function fillTasksList(data){

	var taskLi, taskChkbx, taskVal, taskBtn, taskTrsh;

	var tasklist = document.getElementById("tasks");
	$('#tasks').empty();

	for (i = 0; i < data.length; i += 1) {
		var id_task = data[i]["id_task"];

		taskLi = document.createElement("li");
		taskLi.setAttribute("class", "task");
		taskLi.setAttribute("id", "task-id " + data[i]["id_task"]);
		//CHECKBOX
		taskChkbx = document.createElement("input");
		taskChkbx.setAttribute("type", "checkbox");
		taskChkbx.setAttribute("id", "task-checkbox-id " + data[i]["id_task"]);
		if (data[i]["status"] == "true"){
			taskChkbx.setAttribute('checked', 'checked');
		} else {
			taskChkbx.removeAttribute('checked');
		}
		(function (id_task) {
			console.log(id_task);
			taskChkbx.addEventListener("click", function(){changeStatus(id_task, taskChkbx.checked)});
		})(id_task);
		//USER TASK
		taskVal = document.createTextNode(data[i]["text"]);
		//DELETE BUTTON
		taskBtn = document.createElement("button");
		taskBtn.setAttribute("id", "task-button-id " + data[i]["id_task"]);
		(function (id_task) {
			console.log(id_task);
			taskBtn.addEventListener("click", function(){deleteTask(id_task)});
		})(id_task);
		//TRASH ICON
		taskTrsh = document.createElement("i");
		taskTrsh.setAttribute("class", "fa fa-trash");
		//INSTERT TRASH CAN INTO BUTTON
		taskBtn.appendChild(taskTrsh);

		//APPEND ELEMENTS TO TASKLI
		taskLi.appendChild(taskChkbx);
		taskLi.appendChild(taskVal);
		taskLi.appendChild(taskBtn);
	
		tasklist.appendChild(taskLi);
	}
};

$(document).ready(function(){
	$('#logout').click(function(){
		$.ajax({
			url:"../api.php?action=logout",
			method:"GET",
			dataType: 'json',
			cache:false,
			success:function(){
				localStorage.removeItem('token');
				window.location="auth.php";
			},
			error:function(){ 
			}
		});
	});
});


(function() {
	'use strict';
	var tasker = {
		init: function() {
			this.cacheDom();
			this.bindEvents();
		},
		cacheDom: function() {
			this.taskInput = document.getElementById("input-task");
			this.addBtn = document.getElementById("add-task-btn");
			this.tasklist = document.getElementById("tasks");
			this.tasklistChildren = this.tasklist.children;
			this.errorMessage = document.getElementById("error");
		},
		bindEvents: function() {
			this.addBtn.onclick = this.addTask.bind(this);
			this.taskInput.onkeypress = this.enterKey.bind(this);
		},
		enterKey: function(event) {
			if (event.keyCode === 13 || event.which === 13) {
				this.addTask();
			}
		},
		addTask: function() {
			var value = this.taskInput.value;
			this.errorMessage.style.display = "none";

			if (value === "") {
				this.error();
			} else {
				sendTask();
				this.taskInput.value = "";
			}
		},
		error: function() {
			this.errorMessage.style.display = "block";
		}
	};

	tasker.init();
}());


