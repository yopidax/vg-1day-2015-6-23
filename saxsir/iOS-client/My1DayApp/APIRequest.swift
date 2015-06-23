//
//  APIRequest.swift
//  My1DayApp
//
//  Created by 清 貴幸 on 2015/05/11.
//  Copyright (c) 2015年 VOYAGE GROUP, inc. All rights reserved.
//

import Foundation

class APIRequest {
    static let baseURLString = "http://localhost:8888/"
    
    class func getMessages(completionHandler: ((NSData!, NSURLResponse!, NSError!) -> Void)?) {
        let URL = NSURL(string: APIRequest.baseURLString + Endpoint.Messages.rawValue)!
        let request = NSURLRequest(URL: URL)
        let session = NSURLSession.sharedSession()
        let task = session.dataTaskWithRequest(request, completionHandler: completionHandler)
        task.resume()
    }
    
    // Mission1-4 画像を投稿できるようにする
    class func postMessage(message: String, username: String, completionHandler: ((NSData!, NSURLResponse!, NSError!) -> Void)?) {
        let request = NSMutableURLRequest(URL: NSURL(string: APIRequest.baseURLString + Endpoint.Messages.rawValue)!)
        request.HTTPMethod = "POST"
        request.setValue("application/json; charset=utf-8", forHTTPHeaderField: "Content-Type")

        var error: NSError?
        request.HTTPBody = NSJSONSerialization.dataWithJSONObject(["username":username, "body":message], options: NSJSONWritingOptions.allZeros, error: &error)
        
        if error != nil{
            println(error)
            return
        }
        
        let session = NSURLSession.sharedSession()
        let task = session.dataTaskWithRequest(request, completionHandler: completionHandler)
        task.resume()
    }
    
    class func deleteMessage(messageID: String, completionHandler: ((NSData!, NSURLResponse!, NSError!) -> Void)?) {
        let request = NSMutableURLRequest(URL: NSURL(string: APIRequest.baseURLString + Endpoint.Messages.rawValue + "/" + messageID)!)
        request.HTTPMethod = "DELETE"
        
        let session = NSURLSession.sharedSession()
        let task = session.dataTaskWithRequest(request, completionHandler: completionHandler)
        task.resume()
    }

    enum Endpoint: String {
        case Messages = "messages"
    }
}