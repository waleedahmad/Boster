import React from "react";
import Posts from './posts/Posts';
import EditPost from './posts/EditPost';
import Config from './config/Config';
import Sidebar from './Sidebar';
import Navbar from './Navbar';
import {Route} from 'react-router';

class Root extends React.Component{

    constructor(props){
        super(props);
        this.state = {
            sidebar : {
                counts : {
                    tumblr : 0,
                    facebook : 0,
                    pinterest : 0
                }
            }
        };
        this.getPostsCount();
    }

    updatePostsCount(counts){
        this.setState({
            sidebar : {
                counts : counts
            }
        })
    }

    getPostsCount(){
        $.ajax({
            type : 'GET',
            url : '/api/posts/count',
            success : function(count){
                this.updatePostsCount(count);
            }.bind(this)
        });
    }

    render(){
        return (
            <div>
                <Navbar counts={this.state.sidebar.counts} countUpdate={this.getPostsCount.bind(this)}/>
                <Route path="/" render={(props) => (
                    <Sidebar counts={this.state.sidebar.counts} {...props}/>
                )}/>
                <Route exact path="/(|posts/facebook|posts/pinterest)" render={(props) => (
                    <Posts countUpdate={this.getPostsCount.bind(this)} {...props}/>
                )}/>
                <Route exact path="/post/edit/:id" render={(props) => (
                    <EditPost {...props}/>
                )}/>
                <Route path="/settings" component={Config} />
            </div>
        );
    }
}

export default Root;